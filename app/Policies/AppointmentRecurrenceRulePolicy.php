<?php

declare(strict_types=1);

namespace Modules\Appointment\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentRecurrenceRulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny AppointmentRecurrenceRule');
    }

    public function view(AuthUser $authUser, AppointmentRecurrenceRule $appointmentRecurrenceRule): bool
    {
        return $authUser->can('View AppointmentRecurrenceRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AppointmentRecurrenceRule');
    }

    public function update(AuthUser $authUser, AppointmentRecurrenceRule $appointmentRecurrenceRule): bool
    {
        return $authUser->can('Update AppointmentRecurrenceRule');
    }

    public function delete(AuthUser $authUser, AppointmentRecurrenceRule $appointmentRecurrenceRule): bool
    {
        return $authUser->can('Delete AppointmentRecurrenceRule');
    }

    public function restore(AuthUser $authUser, AppointmentRecurrenceRule $appointmentRecurrenceRule): bool
    {
        return $authUser->can('Restore AppointmentRecurrenceRule');
    }

    public function forceDelete(AuthUser $authUser, AppointmentRecurrenceRule $appointmentRecurrenceRule): bool
    {
        return $authUser->can('ForceDelete AppointmentRecurrenceRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AppointmentRecurrenceRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AppointmentRecurrenceRule');
    }

    public function replicate(AuthUser $authUser, AppointmentRecurrenceRule $appointmentRecurrenceRule): bool
    {
        return $authUser->can('Replicate AppointmentRecurrenceRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AppointmentRecurrenceRule');
    }

}