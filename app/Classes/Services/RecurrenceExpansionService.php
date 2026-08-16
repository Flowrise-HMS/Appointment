<?php

namespace Modules\Appointment\Classes\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;

class RecurrenceExpansionService
{
    public function expand(AppointmentRecurrenceRule $rule, array $overrides = []): Collection
    {
        if (! $rule->is_active) {
            return collect();
        }

        $parent = $rule->appointment;

        return $this->scheduledDates($rule)->map(function (Carbon $date) use ($rule, $parent, $overrides) {
            $reference = $this->instanceReference($rule, $date);

            if (Appointment::query()
                ->where('branch_id', $parent->branch_id)
                ->where('external_reference', $reference)
                ->exists()) {
                return null;
            }

            $start = $date->copy()->setTimeFromTimeString($parent->start_at->format('H:i'));
            $duration = max(30, $parent->start_at->diffInMinutes($parent->end_at));
            $end = $start->copy()->addMinutes($duration);

            if ($this->hasConflict($parent, $start, $end)) {
                Log::info('RecurrenceExpansion: slot conflict, skipping instance', [
                    'rule_id' => $rule->id,
                    'date' => $date->toDateString(),
                ]);

                return null;
            }

            return Appointment::create(array_merge([
                'branch_id' => $parent->branch_id,
                'patient_id' => $parent->patient_id,
                'practitioner_primary_id' => $parent->practitioner_primary_id,
                'status' => AppointmentStatus::BOOKED,
                'appointment_type' => $parent->appointment_type,
                'start_at' => $start,
                'end_at' => $end,
                'external_reference' => $reference,
            ], $overrides));
        })->filter()->values();
    }

    public function cancelSeries(AppointmentRecurrenceRule $rule, string $reasonCode = 'series_cancelled'): int
    {
        $rule->forceFill(['is_active' => false])->save();

        return Appointment::query()
            ->where('branch_id', $rule->branch_id)
            ->where('external_reference', 'like', 'recur:'.$rule->id.':%')
            ->where('start_at', '>', now())
            ->whereIn('status', [AppointmentStatus::BOOKED, AppointmentStatus::ARRIVED])
            ->update([
                'status' => AppointmentStatus::CANCELLED,
                'cancellation_reason_code' => $reasonCode,
            ]);
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function scheduledDates(AppointmentRecurrenceRule $rule): Collection
    {
        $dates = collect();
        $cursor = $rule->appointment->start_at->copy();

        $guard = 0;

        while ($guard++ < 500) {
            match ($rule->frequency) {
                'daily' => $cursor->addDays($rule->interval),
                'weekly' => $cursor->addWeeks($rule->interval),
                'monthly' => $cursor->addMonths($rule->interval),
                default => $cursor->addWeeks($rule->interval),
            };

            if ($rule->until_at !== null && $cursor->gt($rule->until_at)) {
                break;
            }

            $dates->push($cursor->copy());

            if ($rule->occurrence_count !== null && $dates->count() >= $rule->occurrence_count) {
                break;
            }
        }

        return $dates;
    }

    private function instanceReference(AppointmentRecurrenceRule $rule, Carbon $date): string
    {
        return 'recur:'.$rule->id.':'.$date->toDateString();
    }

    private function hasConflict(Appointment $parent, Carbon $start, Carbon $end): bool
    {
        if ($parent->practitioner_primary_id === null) {
            return false;
        }

        return Appointment::query()
            ->where('branch_id', $parent->branch_id)
            ->where('practitioner_primary_id', $parent->practitioner_primary_id)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->where('status', '!=', AppointmentStatus::CANCELLED)
            ->whereKeyNot($parent->getKey())
            ->exists();
    }
}
