<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListScheduleBlockActivities extends ListActivitiesBySubject
{
    protected static string $resource = ScheduleBlockResource::class;
}
