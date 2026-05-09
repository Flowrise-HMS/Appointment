<?php

declare(strict_types=1);

namespace Modules\Appointment\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Appointment\Models\ScheduleBlock;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduleBlockPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny ScheduleBlock');
    }

    public function view(AuthUser $authUser, ScheduleBlock $scheduleBlock): bool
    {
        return $authUser->can('View ScheduleBlock');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create ScheduleBlock');
    }

    public function update(AuthUser $authUser, ScheduleBlock $scheduleBlock): bool
    {
        return $authUser->can('Update ScheduleBlock');
    }

    public function delete(AuthUser $authUser, ScheduleBlock $scheduleBlock): bool
    {
        return $authUser->can('Delete ScheduleBlock');
    }

    public function restore(AuthUser $authUser, ScheduleBlock $scheduleBlock): bool
    {
        return $authUser->can('Restore ScheduleBlock');
    }

    public function forceDelete(AuthUser $authUser, ScheduleBlock $scheduleBlock): bool
    {
        return $authUser->can('ForceDelete ScheduleBlock');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny ScheduleBlock');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny ScheduleBlock');
    }

    public function replicate(AuthUser $authUser, ScheduleBlock $scheduleBlock): bool
    {
        return $authUser->can('Replicate ScheduleBlock');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder ScheduleBlock');
    }

}