<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Database\Factories\BranchFactory;
use Modules\Patient\Database\Factories\PatientFactory;
use Modules\Patient\Models\Patient;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AppointmentGlobalSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateModules(['Core', 'Patient', 'Appointment']);

        Permission::findOrCreate('ViewAny Appointment', 'web');
        Permission::findOrCreate('View Appointment', 'web');
    }

    public function test_appointments_are_found_by_patient_name_with_patient_titled_result(): void
    {
        $branch = BranchFactory::new()->create();
        $this->actingAs(
            User::factory()->create(['branch_id' => $branch->id])
                ->givePermissionTo('ViewAny Appointment', 'View Appointment')
        );

        $patient = Patient::withoutEvents(fn () => PatientFactory::new()->create([
            'branch_id' => $branch->id,
            'title' => null,
            'first_name' => 'Zainab',
            'middle_name' => null,
            'last_name' => 'Fuseini',
        ]));

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'branch_id' => $branch->id,
            'reason_text' => 'Routine antenatal review',
        ]);

        $results = AppointmentResource::getGlobalSearchResults('Fuseini');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('Zainab Fuseini', $results->first()->title);
        $this->assertSame('Routine antenatal review', $results->first()->details['Reason'] ?? null);

        $this->assertCount(1, AppointmentResource::getGlobalSearchResults('antenatal'));
        $this->assertCount(0, AppointmentResource::getGlobalSearchResults('no-such-appointment'));
    }
}
