<?php

namespace Modules\Appointment\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSyncOutbox;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Tests\TestCase;

class AppointmentSchedulingServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AppointmentSchedulingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateModules(['Core', 'Patient', 'Appointment']);
        $this->service = app(AppointmentSchedulingService::class);
    }

    public function test_schedule_creates_appointment_and_sync_outbox_entry(): void
    {
        $branch = Branch::factory()->create();
        $patient = Patient::factory()->create(['branch_id' => $branch->id]);
        $startAt = now()->addDay()->setTime(9, 0);
        $endAt = $startAt->copy()->addMinutes(30);

        $appointment = $this->service->schedule([
            'branch_id' => $branch->id,
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertSame(AppointmentStatus::BOOKED, $appointment->status);

        $this->assertTrue(
            AppointmentSyncOutbox::query()
                ->where('aggregate_id', $appointment->id)
                ->where('event_name', 'appointment.booked')
                ->exists()
        );
    }

    public function test_check_in_marks_appointment_arrived(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => AppointmentStatus::BOOKED,
        ]);

        $checkedIn = $this->service->checkIn($appointment);

        $this->assertSame(AppointmentStatus::ARRIVED, $checkedIn->status);
        $this->assertNotNull($checkedIn->checked_in_at);
    }
}
