<?php

namespace Modules\Appointment\Classes\Services;

use Illuminate\Support\Facades\DB;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\SyncOutboxStatus;
use Modules\Appointment\Events\AppointmentCheckedIn;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSyncOutbox;

class AppointmentSchedulingService
{
    public function __construct(protected AppointmentConflictService $conflictService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function schedule(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {
            $this->assertNoConflicts($data);
            $appointment = Appointment::create($data);
            $this->pushOutbox($appointment, 'appointment.booked');

            return $appointment->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function reschedule(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            $this->assertNoConflicts($data, $appointment);
            $appointment->update($data);
            $appointment->increment('version');
            $this->pushOutbox($appointment->fresh(), 'appointment.rescheduled');

            return $appointment->fresh();
        });
    }

    public function checkIn(Appointment $appointment): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::ARRIVED,
            'checked_in_at' => now(),
        ]);
        $appointment->increment('version');
        $this->pushOutbox($appointment->fresh(), 'appointment.checked_in');

        event(new AppointmentCheckedIn($appointment->id));

        return $appointment->fresh();
    }

    public function cancel(Appointment $appointment, ?string $reasonCode = null): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
            'cancellation_reason_code' => $reasonCode,
        ]);
        $appointment->increment('version');
        $this->pushOutbox($appointment->fresh(), 'appointment.cancelled');

        return $appointment->fresh();
    }

    protected function pushOutbox(Appointment $appointment, string $eventName): void
    {
        $idempotencyRaw = "{$appointment->id}|{$eventName}|{$appointment->version}";
        $idempotencyKey = hash('sha256', $idempotencyRaw);

        AppointmentSyncOutbox::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'branch_id' => $appointment->branch_id,
                'aggregate_type' => AppointmentSyncOutbox::AGGREGATE_TYPE_APPOINTMENT,
                'aggregate_id' => $appointment->id,
                'event_name' => $eventName,
                'payload' => [
                    'appointment_id' => $appointment->id,
                    'status' => $appointment->status->value,
                    'start_at' => optional($appointment->start_at)->toIso8601String(),
                    'end_at' => optional($appointment->end_at)->toIso8601String(),
                ],
                'available_at' => now(),
                'status' => SyncOutboxStatus::PENDING,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function assertNoConflicts(array $data, ?Appointment $appointment = null): void
    {
        if (! isset($data['start_at'], $data['end_at'])) {
            return;
        }

        $branchId = (string) ($data['branch_id'] ?? $appointment?->branch_id);
        $practitionerId = $data['practitioner_primary_id'] ?? $appointment?->practitioner_primary_id;

        if ($this->conflictService->hasPractitionerConflict(
            $branchId,
            $practitionerId,
            (string) $data['start_at'],
            (string) $data['end_at'],
            $appointment?->id
        )) {
            abort(422, 'Practitioner has a scheduling conflict for the selected slot.');
        }
    }
}
