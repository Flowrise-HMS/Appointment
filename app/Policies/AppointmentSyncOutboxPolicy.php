<?php

declare(strict_types=1);

namespace Modules\Appointment\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Appointment\Models\AppointmentSyncOutbox;

class AppointmentSyncOutboxPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny AppointmentSyncOutbox');
    }

    public function view(AuthUser $authUser, AppointmentSyncOutbox $appointmentSyncOutbox): bool
    {
        return $authUser->can('View AppointmentSyncOutbox');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AppointmentSyncOutbox');
    }

    public function update(AuthUser $authUser, AppointmentSyncOutbox $appointmentSyncOutbox): bool
    {
        return $authUser->can('Update AppointmentSyncOutbox');
    }

    public function delete(AuthUser $authUser, AppointmentSyncOutbox $appointmentSyncOutbox): bool
    {
        return $authUser->can('Delete AppointmentSyncOutbox');
    }

    public function restore(AuthUser $authUser, AppointmentSyncOutbox $appointmentSyncOutbox): bool
    {
        return $authUser->can('Restore AppointmentSyncOutbox');
    }

    public function forceDelete(AuthUser $authUser, AppointmentSyncOutbox $appointmentSyncOutbox): bool
    {
        return $authUser->can('ForceDelete AppointmentSyncOutbox');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AppointmentSyncOutbox');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AppointmentSyncOutbox');
    }

    public function replicate(AuthUser $authUser, AppointmentSyncOutbox $appointmentSyncOutbox): bool
    {
        return $authUser->can('Replicate AppointmentSyncOutbox');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AppointmentSyncOutbox');
    }
}
