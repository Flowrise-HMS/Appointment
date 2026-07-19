<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Models\Branch;
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
}
