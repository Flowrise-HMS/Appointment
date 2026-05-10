<?php

declare(strict_types=1);

namespace Modules\Appointment\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Appointment\Models\AppointmentAudit;

class AppointmentAuditPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny AppointmentAudit');
    }

    public function view(AuthUser $authUser, AppointmentAudit $appointmentAudit): bool
    {
        return $authUser->can('View AppointmentAudit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AppointmentAudit');
    }

    public function update(AuthUser $authUser, AppointmentAudit $appointmentAudit): bool
    {
        return $authUser->can('Update AppointmentAudit');
    }

    public function delete(AuthUser $authUser, AppointmentAudit $appointmentAudit): bool
    {
        return $authUser->can('Delete AppointmentAudit');
    }

    public function restore(AuthUser $authUser, AppointmentAudit $appointmentAudit): bool
    {
        return $authUser->can('Restore AppointmentAudit');
    }

    public function forceDelete(AuthUser $authUser, AppointmentAudit $appointmentAudit): bool
    {
        return $authUser->can('ForceDelete AppointmentAudit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AppointmentAudit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AppointmentAudit');
    }

    public function replicate(AuthUser $authUser, AppointmentAudit $appointmentAudit): bool
    {
        return $authUser->can('Replicate AppointmentAudit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AppointmentAudit');
    }
}
