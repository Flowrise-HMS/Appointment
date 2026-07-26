<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;
use Modules\Core\Filament\Pages\Concerns\RestrictsActivitiesToSuperAdmin;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListScheduleBlockActivities extends ListActivitiesBySubject
{
    use RestrictsActivitiesToSuperAdmin;

    protected static string $resource = ScheduleBlockResource::class;
}
