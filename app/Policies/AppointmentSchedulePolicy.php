<?php

declare(strict_types=1);

namespace Modules\Appointment\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Appointment\Models\AppointmentSchedule;

class AppointmentSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny AppointmentSchedule');
    }

    public function view(AuthUser $authUser, AppointmentSchedule $appointmentSchedule): bool
    {
        return $authUser->can('View AppointmentSchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AppointmentSchedule');
    }

    public function update(AuthUser $authUser, AppointmentSchedule $appointmentSchedule): bool
    {
        return $authUser->can('Update AppointmentSchedule');
    }

    public function delete(AuthUser $authUser, AppointmentSchedule $appointmentSchedule): bool
    {
        return $authUser->can('Delete AppointmentSchedule');
    }

    public function restore(AuthUser $authUser, AppointmentSchedule $appointmentSchedule): bool
    {
        return $authUser->can('Restore AppointmentSchedule');
    }

    public function forceDelete(AuthUser $authUser, AppointmentSchedule $appointmentSchedule): bool
    {
        return $authUser->can('ForceDelete AppointmentSchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AppointmentSchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AppointmentSchedule');
    }

    public function replicate(AuthUser $authUser, AppointmentSchedule $appointmentSchedule): bool
    {
        return $authUser->can('Replicate AppointmentSchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AppointmentSchedule');
    }
}
