<?php

namespace Modules\Appointment\Policies;

use App\Models\User;
use Modules\Appointment\Models\Appointment;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny Appointment') || $user->can('view_any_appointment');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return ($user->can('View Appointment') || $user->can('view_appointment'))
            && $user->branch_id === $appointment->branch_id;
    }

    public function create(User $user): bool
    {
        return $user->can('Create Appointment') || $user->can('create_appointment');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return ($user->can('Update Appointment') || $user->can('update_appointment'))
            && $user->branch_id === $appointment->branch_id;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return ($user->can('Delete Appointment') || $user->can('delete_appointment'))
            && $user->branch_id === $appointment->branch_id;
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return ($user->can('Restore Appointment') || $user->can('restore_appointment'))
            && $user->branch_id === $appointment->branch_id;
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return ($user->can('ForceDelete Appointment') || $user->can('force_delete_appointment'))
            && $user->branch_id === $appointment->branch_id;
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
            && $user->branch_id === $appointment->branch_id;
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder Appointment') || $user->can('reorder_appointment');
    }
}
