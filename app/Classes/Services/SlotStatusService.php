<?php

namespace Modules\Appointment\Classes\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSlot;
use Modules\Appointment\Models\ScheduleBlock;

/**
 * Derives each slot's status from what overlaps it:
 *
 * - blocked: a schedule block for the slot's practitioner, or a block with no
 *   practitioner that is branch-wide or on the schedule's location;
 * - busy: a live (not cancelled / no-show) appointment for the slot's
 *   practitioner, or — for schedules without a practitioner — for its service;
 * - free otherwise.
 *
 * Status is recomputed from scratch, so it is safe to call repeatedly.
 */
class SlotStatusService
{
    /**
     * @param  iterable<AppointmentSlot>  $slots
     */
    public function refresh(iterable $slots): int
    {
        $changed = 0;

        foreach ($slots as $slot) {
            $slot->loadMissing('schedule');
            [$status, $appointmentId] = $this->resolve($slot);

            if ($slot->status !== $status || $slot->appointment_id !== $appointmentId) {
                $slot->forceFill(['status' => $status, 'appointment_id' => $appointmentId])->save();
                $changed++;
            }
        }

        return $changed;
    }

    /**
     * Refresh the slots an appointment touches, before and after a change.
     */
    public function refreshForAppointment(Appointment $appointment): int
    {
        $windows = [[
            'branch' => $appointment->branch_id,
            'practitioner' => $appointment->practitioner_primary_id,
            'service' => $appointment->service_id,
            'start' => $appointment->start_at,
            'end' => $appointment->end_at,
        ]];

        $original = $appointment->getOriginal();
        if ($appointment->wasChanged(['start_at', 'end_at', 'practitioner_primary_id', 'service_id', 'branch_id'])
            || $appointment->isDirty(['start_at', 'end_at', 'practitioner_primary_id', 'service_id', 'branch_id'])) {
            $windows[] = [
                'branch' => $original['branch_id'] ?? null,
                'practitioner' => $original['practitioner_primary_id'] ?? null,
                'service' => $original['service_id'] ?? null,
                'start' => isset($original['start_at']) ? Carbon::parse($original['start_at']) : null,
                'end' => isset($original['end_at']) ? Carbon::parse($original['end_at']) : null,
            ];
        }

        $slots = collect();

        foreach ($windows as $window) {
            if ($window['branch'] === null || $window['start'] === null || $window['end'] === null) {
                continue;
            }

            $slots = $slots->merge(
                $this->query()
                    ->where('branch_id', $window['branch'])
                    ->overlapping($window['start'], $window['end'])
                    ->where(function (Builder $query) use ($window): void {
                        $query->when($window['practitioner'], fn (Builder $q) => $q->orWhere('practitioner_id', $window['practitioner']))
                            ->when($window['service'], fn (Builder $q) => $q->orWhere(fn (Builder $s) => $s->whereNull('practitioner_id')->where('service_id', $window['service'])));
                    })
                    ->when(! $window['practitioner'] && ! $window['service'], fn (Builder $q) => $q->whereRaw('1 = 0'))
                    ->get()
            );
        }

        $slots = $slots->merge($this->query()->where('appointment_id', $appointment->getKey())->get());

        return $this->refresh($slots->unique('id'));
    }

    /**
     * Refresh the slots a schedule block touches, before and after a change.
     */
    public function refreshForBlock(ScheduleBlock $block): int
    {
        $windows = [[$block->branch_id, $block->blocked_from, $block->blocked_to]];
        $original = $block->getOriginal();

        if (isset($original['blocked_from'], $original['blocked_to'])) {
            $windows[] = [$original['branch_id'] ?? $block->branch_id, Carbon::parse($original['blocked_from']), Carbon::parse($original['blocked_to'])];
        }

        $slots = collect();

        foreach ($windows as [$branchId, $from, $to]) {
            if ($branchId === null || $from === null || $to === null) {
                continue;
            }

            $slots = $slots->merge($this->query()->where('branch_id', $branchId)->overlapping(Carbon::parse($from), Carbon::parse($to))->get());
        }

        return $this->refresh($slots->unique('id'));
    }

    /**
     * @return array{0: SlotStatus, 1: string|null}
     */
    private function resolve(AppointmentSlot $slot): array
    {
        if ($this->blockingBlocks($slot)->exists()) {
            return [SlotStatus::BLOCKED, null];
        }

        $appointment = $this->overlappingAppointments($slot)->orderBy('start_at')->first(['id']);

        return $appointment !== null
            ? [SlotStatus::BUSY, (string) $appointment->getKey()]
            : [SlotStatus::FREE, null];
    }

    /**
     * @return Builder<ScheduleBlock>
     */
    private function blockingBlocks(AppointmentSlot $slot): Builder
    {
        $locationId = $slot->schedule?->location_id;

        return ScheduleBlock::query()
            ->withoutGlobalScope('branch')
            ->where('branch_id', $slot->branch_id)
            ->where('blocked_from', '<', $slot->end_at)
            ->where('blocked_to', '>', $slot->start_at)
            ->where(function (Builder $query) use ($slot, $locationId): void {
                if ($slot->practitioner_id !== null) {
                    $query->orWhere('practitioner_id', $slot->practitioner_id);
                }

                $query->orWhere(function (Builder $q) use ($locationId): void {
                    $q->whereNull('practitioner_id')
                        ->where(fn (Builder $l) => $l->whereNull('location_id')->when($locationId, fn (Builder $x) => $x->orWhere('location_id', $locationId)));
                });
            });
    }

    /**
     * @return Builder<Appointment>
     */
    private function overlappingAppointments(AppointmentSlot $slot): Builder
    {
        return Appointment::query()
            ->withoutGlobalScope('branch')
            ->where('branch_id', $slot->branch_id)
            ->whereNull('deleted_at')
            ->whereNotIn('status', [AppointmentStatus::CANCELLED, AppointmentStatus::NOSHOW])
            ->where('start_at', '<', $slot->end_at)
            ->where('end_at', '>', $slot->start_at)
            ->where(function (Builder $query) use ($slot): void {
                if ($slot->practitioner_id !== null) {
                    $query->where('practitioner_primary_id', $slot->practitioner_id);
                } elseif ($slot->service_id !== null) {
                    $query->where('service_id', $slot->service_id);
                } else {
                    $query->whereKey($slot->appointment_id ?? '');
                }
            });
    }

    /**
     * @return Builder<AppointmentSlot>
     */
    private function query(): Builder
    {
        return AppointmentSlot::query()->withoutGlobalScope('branch');
    }
}
