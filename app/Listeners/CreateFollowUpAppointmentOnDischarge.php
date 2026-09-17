<?php

namespace Modules\Appointment\Listeners;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Models\Appointment;
use Modules\Clinical\Events\PatientDischarged;

/**
 * Books the follow-up chosen at discharge. Idempotent per encounter so a
 * replayed event never creates a second appointment.
 */
class CreateFollowUpAppointmentOnDischarge
{
    public function __construct(protected AppointmentSchedulingService $scheduling) {}

    public function handle(PatientDischarged $event): void
    {
        if ($event->followUpAt === null) {
            return;
        }

        $encounter = $event->encounter;
        $key = 'discharge-follow-up:'.$encounter->id;

        if (Appointment::query()->where('idempotency_key', $key)->exists()) {
            return;
        }

        $duration = (int) config('appointment.follow_up_duration_minutes', 20);

        try {
            $this->scheduling->schedule([
                'branch_id' => $encounter->branch_id,
                'patient_id' => $encounter->patient_id,
                'practitioner_primary_id' => $event->followUpProviderId,
                'department_id' => $encounter->department_id,
                'status' => AppointmentStatus::BOOKED,
                'appointment_type' => AppointmentType::FOLLOW_UP,
                'reason_text' => __('Post-discharge follow-up (:number)', ['number' => $encounter->encounter_number]),
                'start_at' => $event->followUpAt,
                'end_at' => $event->followUpAt->copy()->addMinutes($duration),
                'created_by' => $encounter->discharged_by,
                'idempotency_key' => $key,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Follow-up appointment could not be booked on discharge', [
                'encounter_id' => $encounter->id,
                'error' => $e->getMessage(),
            ]);

            $discharger = $encounter->dischargedBy;

            if ($discharger !== null) {
                Notification::make()
                    ->title(__('Follow-up could not be auto-booked'))
                    ->body(__(':patient — :reason', ['patient' => $encounter->patient?->full_name ?? $encounter->encounter_number, 'reason' => $e->getMessage()]))
                    ->warning()
                    ->sendToDatabase($discharger);
            }
        }
    }
}
