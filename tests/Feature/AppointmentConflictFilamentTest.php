<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\CreateAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\EditAppointment;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Modules\Staff\Models\Staff;
use Tests\TestCase;

/**
 * The Filament create/edit pages used to write Appointment rows directly,
 * skipping the practitioner conflict check the API and the clinical quick
 * actions enforce through AppointmentSchedulingService.
 */
class AppointmentConflictFilamentTest extends TestCase
{
    use DatabaseTransactions;

    private Branch $branch;

    private Patient $patient;

    private Staff $practitioner;

    private Appointment $existing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
        Gate::before(fn () => true);

        $this->branch = Branch::factory()->create();
        $this->patient = Patient::withoutEvents(fn () => Patient::factory()->create(['branch_id' => $this->branch->id, 'mrn' => 'FR-TEST-00001']));
        $this->practitioner = Staff::factory()->create(['branch_id' => $this->branch->id]);
        $this->existing = Appointment::factory()->create([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'practitioner_primary_id' => $this->practitioner->id,
            'start_at' => now()->addDay()->setTime(9, 0),
            'end_at' => now()->addDay()->setTime(9, 30),
        ]);

        Livewire::actingAs(User::factory()->create(['branch_id' => $this->branch->id]));
    }

    public function test_create_page_rejects_an_overlapping_slot_for_the_same_practitioner(): void
    {
        Livewire::test(CreateAppointment::class)
            ->fillForm([
                'patient_id' => $this->patient->id,
                'branch_id' => $this->branch->id,
                'practitioner_primary_id' => $this->practitioner->id,
                'status' => AppointmentStatus::BOOKED->value,
                'appointment_type' => AppointmentType::OUTPATIENT->value,
                'priority' => 5,
                'start_at' => now()->addDay()->setTime(9, 15),
                'end_at' => now()->addDay()->setTime(9, 45),
            ])
            ->call('create')
            ->assertNotified(__('Scheduling conflict'));

        $this->assertSame(1, Appointment::query()->count());
    }

    public function test_create_page_books_a_free_slot_and_writes_the_sync_outbox(): void
    {
        Livewire::test(CreateAppointment::class)
            ->fillForm([
                'patient_id' => $this->patient->id,
                'branch_id' => $this->branch->id,
                'practitioner_primary_id' => $this->practitioner->id,
                'status' => AppointmentStatus::BOOKED->value,
                'appointment_type' => AppointmentType::OUTPATIENT->value,
                'priority' => 5,
                'start_at' => now()->addDay()->setTime(10, 0),
                'end_at' => now()->addDay()->setTime(10, 30),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, Appointment::query()->count());
        $this->assertDatabaseHas('appointment_sync_outbox', ['event_name' => 'appointment.booked']);
    }

    public function test_edit_page_rejects_moving_into_an_occupied_slot(): void
    {
        $other = Appointment::factory()->create([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'practitioner_primary_id' => $this->practitioner->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(11, 30),
        ]);

        Livewire::test(EditAppointment::class, ['record' => $other->getRouteKey()])
            ->fillForm([
                'start_at' => now()->addDay()->setTime(9, 10),
                'end_at' => now()->addDay()->setTime(9, 40),
            ])
            ->call('save')
            ->assertNotified(__('Scheduling conflict'));

        $this->assertSame(11, $other->fresh()->start_at->hour);
    }
}
