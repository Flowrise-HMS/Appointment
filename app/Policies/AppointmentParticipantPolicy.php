<?php

declare(strict_types=1);

namespace Modules\Appointment\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Appointment\Models\AppointmentParticipant;

class AppointmentParticipantPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny AppointmentParticipant');
    }

    public function view(AuthUser $authUser, AppointmentParticipant $appointmentParticipant): bool
    {
        return $authUser->can('View AppointmentParticipant');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AppointmentParticipant');
    }

    public function update(AuthUser $authUser, AppointmentParticipant $appointmentParticipant): bool
    {
        return $authUser->can('Update AppointmentParticipant');
    }

    public function delete(AuthUser $authUser, AppointmentParticipant $appointmentParticipant): bool
    {
        return $authUser->can('Delete AppointmentParticipant');
    }

    public function restore(AuthUser $authUser, AppointmentParticipant $appointmentParticipant): bool
    {
        return $authUser->can('Restore AppointmentParticipant');
    }

    public function forceDelete(AuthUser $authUser, AppointmentParticipant $appointmentParticipant): bool
    {
        return $authUser->can('ForceDelete AppointmentParticipant');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AppointmentParticipant');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AppointmentParticipant');
    }

    public function replicate(AuthUser $authUser, AppointmentParticipant $appointmentParticipant): bool
    {
        return $authUser->can('Replicate AppointmentParticipant');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AppointmentParticipant');
    }
}
