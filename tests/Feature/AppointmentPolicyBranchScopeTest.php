<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Models\Branch;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Appointment abilities are scoped to the branch the user is working in, not
 * the raw users.branch_id column, so staff without an assigned branch can still
 * act on appointments in the branch they have selected.
 */
class AppointmentPolicyBranchScopeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateModules(['Core', 'Patient', 'Appointment']);
    }

    public function test_user_without_assigned_branch_can_act_in_session_branch(): void
    {
        $branch = Branch::factory()->default()->create();
        $user = $this->userWithAppointmentPermissions(['branch_id' => null]);
        $appointment = Appointment::factory()->create(['branch_id' => $branch->id]);

        $this->actingAs($user);
        session(['current_branch_id' => $branch->id]);

        $this->assertTrue($user->can('view', $appointment));
        $this->assertTrue($user->can('update', $appointment));
    }

    public function test_user_without_any_branch_context_falls_back_to_default_branch(): void
    {
        $branch = Branch::factory()->default()->create();
        $user = $this->userWithAppointmentPermissions(['branch_id' => null]);
        $appointment = Appointment::factory()->create(['branch_id' => $branch->id]);

        $this->actingAs($user);

        $this->assertTrue($user->can('update', $appointment));
    }

    public function test_session_branch_takes_precedence_over_assigned_branch(): void
    {
        $home = Branch::factory()->default()->create();
        $selected = Branch::factory()->create();
        $user = $this->userWithAppointmentPermissions(['branch_id' => $home->id]);

        $inHome = Appointment::factory()->create(['branch_id' => $home->id]);
        $inSelected = Appointment::factory()->create(['branch_id' => $selected->id]);

        $this->actingAs($user);
        session(['current_branch_id' => $selected->id]);

        $this->assertTrue($user->can('update', $inSelected));
        $this->assertFalse($user->can('update', $inHome));
    }

    public function test_other_branch_appointments_stay_denied(): void
    {
        $branch = Branch::factory()->default()->create();
        $other = Branch::factory()->create();
        $user = $this->userWithAppointmentPermissions(['branch_id' => $branch->id]);
        $appointment = Appointment::factory()->create(['branch_id' => $other->id]);

        $this->actingAs($user);

        $this->assertFalse($user->can('view', $appointment));
        $this->assertFalse($user->can('update', $appointment));
    }

    public function test_permission_is_still_required(): void
    {
        $branch = Branch::factory()->default()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $appointment = Appointment::factory()->create(['branch_id' => $branch->id]);

        $this->actingAs($user);

        $this->assertFalse($user->can('update', $appointment));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function userWithAppointmentPermissions(array $attributes): User
    {
        $user = User::factory()->create($attributes);

        foreach (['View Appointment', 'Update Appointment'] as $permission) {
            Permission::findOrCreate($permission, 'web');
            $user->givePermissionTo($permission);
        }

        return $user;
    }
}
