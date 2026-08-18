<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Appointment\Filament\RelationManagers\PatientAppointmentsRelationManager;
use Modules\Appointment\Filament\RelationManagers\Staff\StaffAppointmentsRelationManager;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Database\Factories\BranchFactory;
use Modules\Patient\Database\Factories\PatientFactory;
use Modules\Patient\Filament\Clusters\Patient\Resources\Patients\Pages\ViewPatient;
use Modules\Patient\Filament\Clusters\Patient\Resources\Patients\PatientResource;
use Modules\Patient\Models\Patient;
use Modules\Staff\Database\Factories\StaffFactory;
use Modules\Staff\Filament\Clusters\StaffCluster\Resources\Staff\Pages\ViewStaff;
use Modules\Staff\Filament\Clusters\StaffCluster\Resources\Staff\StaffResource;
use Tests\TestCase;

class AppointmentRelationManagersTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
    }

    public function test_patient_relation_manager_is_registered_when_appointment_enabled(): void
    {
        $this->assertContains(
            PatientAppointmentsRelationManager::class,
            PatientResource::getRelations(),
        );
    }

    public function test_staff_relation_manager_is_registered_when_appointment_enabled(): void
    {
        $this->assertContains(
            StaffAppointmentsRelationManager::class,
            StaffResource::getRelations(),
        );
    }

    public function test_patient_relation_manager_lists_patient_appointments(): void
    {
        $user = User::factory()->create();
        Gate::before(fn () => true);
        $branch = BranchFactory::new()->create();
        $patient = Patient::withoutEvents(fn () => PatientFactory::new()->create(['branch_id' => $branch->id]));
        Appointment::factory()->create([
            'branch_id' => $branch->id,
            'patient_id' => $patient->id,
        ]);

        Livewire::actingAs($user)
            ->test(PatientAppointmentsRelationManager::class, [
                'ownerRecord' => $patient,
                'pageClass' => ViewPatient::class,
            ])
            ->assertOk()
            ->assertCanSeeTableRecords($patient->appointments);
    }

    public function test_staff_relation_manager_lists_primary_practitioner_appointments(): void
    {
        $user = User::factory()->create();
        Gate::before(fn () => true);
        $branch = BranchFactory::new()->create();
        $patient = Patient::withoutEvents(fn () => PatientFactory::new()->create(['branch_id' => $branch->id]));
        $staff = StaffFactory::new()->create(['branch_id' => $branch->id]);
        Appointment::factory()->create([
            'branch_id' => $branch->id,
            'patient_id' => $patient->id,
            'practitioner_primary_id' => $staff->id,
        ]);

        Livewire::actingAs($user)
            ->test(StaffAppointmentsRelationManager::class, [
                'ownerRecord' => $staff,
                'pageClass' => ViewStaff::class,
            ])
            ->assertOk()
            ->assertCanSeeTableRecords($staff->appointments);
    }
}
