<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Modules\Staff\Models\Staff;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create(['name' => 'ViewAny Appointment', 'guard_name' => 'web']);
        Permission::create(['name' => 'View Appointment', 'guard_name' => 'web']);

        $branch = Branch::factory()->create();

        $this->user = User::factory()->create([
            'branch_id' => $branch->id,
        ]);
    }

    public function test_unauthenticated_request_gets_401(): void
    {
        $response = $this->getJson('/api/v1/appointments');

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    public function test_index_returns_appointments(): void
    {
        $this->user->givePermissionTo('ViewAny Appointment');

        Appointment::factory()->count(3)->create(['branch_id' => $this->user->branch_id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/appointments');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_show_returns_single_appointment(): void
    {
        $this->user->givePermissionTo('View Appointment');

        $appointment = Appointment::factory()->create(['branch_id' => $this->user->branch_id]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/appointments/{$appointment->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $appointment->id);
    }

    public function test_user_without_branch_gets_403(): void
    {
        $userWithoutBranch = User::factory()->create(['branch_id' => null]);

        $response = $this->actingAs($userWithoutBranch)->getJson('/api/v1/appointments');

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_store_returns_422_for_a_practitioner_conflict(): void
    {
        Permission::create(['name' => 'Create Appointment', 'guard_name' => 'web']);
        $this->user->givePermissionTo('Create Appointment');

        $patient = Patient::withoutEvents(fn () => Patient::factory()->create(['branch_id' => $this->user->branch_id]));
        $practitioner = Staff::factory()->create(['branch_id' => $this->user->branch_id]);
        $startAt = now()->addDay()->setTime(9, 0);

        Appointment::factory()->create([
            'branch_id' => $this->user->branch_id,
            'patient_id' => $patient->id,
            'practitioner_primary_id' => $practitioner->id,
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addMinutes(30),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/appointments', [
            'patient_id' => $patient->id,
            'branch_id' => $this->user->branch_id,
            'practitioner_primary_id' => $practitioner->id,
            'start_at' => $startAt->copy()->addMinutes(10)->toIso8601String(),
            'end_at' => $startAt->copy()->addMinutes(40)->toIso8601String(),
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, Appointment::query()->count());
    }
}
