<?php

declare(strict_types=1);

namespace Modules\Appointment\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Appointment\Models\AppointmentSlot;

/**
 * Slots are generated and kept in sync by the system: they can be viewed by
 * anyone who can view schedules, and never written by hand.
 */
class AppointmentSlotPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny AppointmentSchedule');
    }

    public function view(AuthUser $authUser, AppointmentSlot $appointmentSlot): bool
    {
        return $authUser->can('View AppointmentSchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, AppointmentSlot $appointmentSlot): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, AppointmentSlot $appointmentSlot): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}
