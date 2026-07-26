<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\WaitlistEntryResource;
use Modules\Core\Filament\Pages\Concerns\RestrictsActivitiesToSuperAdmin;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListWaitlistEntryActivities extends ListActivitiesBySubject
{
    use RestrictsActivitiesToSuperAdmin;

    protected static string $resource = WaitlistEntryResource::class;
}
