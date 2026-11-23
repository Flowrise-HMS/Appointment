<?php

namespace Modules\Appointment\Classes\Services;

use Illuminate\Support\Carbon;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Appointment\Exceptions\OutsideScheduleException;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\AppointmentSlot;
use Modules\Appointment\Settings\AppointmentSettings;
use Throwable;

/**
 * When a practitioner has an active schedule covering the booking date, the
 * booking must fall entirely within that practitioner's generated slots and
 * none of them may be blocked. Practitioners without a schedule keep the
 * previous behaviour (conflict checks only). Controlled by the Appointments
 * setting "Enforce schedules".
 */
class ScheduleCoverageService
{
    public function __construct(protected SlotGenerationService $generator) {}

    public function assertWithinSchedule(string $branchId, ?string $practitionerId, Carbon $start, Carbon $end): void
    {
        if ($practitionerId === null || ! $this->enforced() || $end->lte($start)) {
            return;
        }

        $schedules = AppointmentSchedule::query()
            ->withoutGlobalScope('branch')
            ->where('branch_id', $branchId)
            ->where('practitioner_id', $practitionerId)
            ->where('is_active', true)
            ->get()
            ->filter(fn (AppointmentSchedule $schedule): bool => $schedule->coversDate($start));

        if ($schedules->isEmpty()) {
            return;
        }

        foreach ($schedules as $schedule) {
            $this->generator->ensureDay($schedule, $start);
        }

        $slots = AppointmentSlot::query()
            ->withoutGlobalScope('branch')
            ->whereIn('schedule_id', $schedules->modelKeys())
            ->overlapping($start, $end)
            ->orderBy('start_at')
            ->get();

        $cursor = $start->copy();

        foreach ($slots as $slot) {
            if ($slot->status === SlotStatus::BLOCKED) {
                break;
            }

            if ($slot->start_at->gt($cursor)) {
                break;
            }

            if ($slot->end_at->gt($cursor)) {
                $cursor = $slot->end_at->copy();
            }

            if ($cursor->gte($end)) {
                return;
            }
        }

        throw new OutsideScheduleException;
    }

    private function enforced(): bool
    {
        try {
            return app(AppointmentSettings::class)->enforce_schedules;
        } catch (Throwable) {
            return true;
        }
    }
}
