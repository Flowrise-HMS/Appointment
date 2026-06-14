<?php

namespace Modules\Appointment\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\Patient\Models\Patient;
use Modules\Core\Models\Branch;
use Tests\TestCase;

class AppointmentModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('module:migrate', ['module' => 'Core', '--force' => true]);
        $this->artisan('module:migrate', ['module' => 'Patient', '--force' => true]);
        $this->artisan('module:migrate', ['module' => 'Appointment', '--force' => true]);
    }

    public function test_appointment_factory_creates_appointment(): void
    {
        $appointment = Appointment::factory()->create();
        $this->assertTrue($appointment->exists);
        $this->assertNotNull($appointment->id);
    }

    public function test_appointment_belongs_to_branch(): void
    {
        $branch = Branch::factory()->create();
        $appointment = Appointment::factory()->create(['branch_id' => $branch->id]);

        $this->assertEquals($branch->id, $appointment->branch->id);
    }

    public function test_appointment_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $this->assertEquals($patient->id, $appointment->patient->id);
    }

    public function test_appointment_has_participants(): void
    {
        $appointment = Appointment::factory()->create();
        AppointmentParticipant::factory()->count(2)->create([
            'appointment_id' => $appointment->id,
        ]);

        $this->assertCount(2, $appointment->participants);
    }
}
