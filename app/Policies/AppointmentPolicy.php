<?php

namespace Modules\Appointment\Policies;

use App\Models\User;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Classes\Services\BranchService;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny Appointment') || $user->can('view_any_appointment');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return ($user->can('View Appointment') || $user->can('view_appointment'))
            && $this->isInCurrentBranch($user, $appointment);
    }

    public function create(User $user): bool
    {
        return $user->can('Create Appointment') || $user->can('create_appointment');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return ($user->can('Update Appointment') || $user->can('update_appointment'))
            && $this->isInCurrentBranch($user, $appointment);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return ($user->can('Delete Appointment') || $user->can('delete_appointment'))
            && $this->isInCurrentBranch($user, $appointment);
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return ($user->can('Restore Appointment') || $user->can('restore_appointment'))
            && $this->isInCurrentBranch($user, $appointment);
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return ($user->can('ForceDelete Appointment') || $user->can('force_delete_appointment'))
            && $this->isInCurrentBranch($user, $appointment);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny Appointment') || $user->can('restore_any_appointment');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny Appointment') || $user->can('force_delete_any_appointment');
    }

    public function replicate(User $user, Appointment $appointment): bool
    {
        return ($user->can('Replicate Appointment') || $user->can('replicate_appointment'))
            && $this->isInCurrentBranch($user, $appointment);
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder Appointment') || $user->can('reorder_appointment');
    }

    /**
     * Appointments are scoped to the branch the user is currently working in.
     * That is the session branch when one is selected, otherwise the user's own
     * branch, otherwise the organisation default — the same resolution the
     * rest of the application uses. Comparing against the raw user branch_id
     * denied everything to users without an assigned branch.
     */
    protected function isInCurrentBranch(User $user, Appointment $appointment): bool
    {
        $currentBranchId = session('current_branch_id', $user->branch_id)
            ?? app(BranchService::class)->getDefaultBranchId();

        return $currentBranchId !== null
            && (string) $currentBranchId === (string) $appointment->branch_id;
    }
}
