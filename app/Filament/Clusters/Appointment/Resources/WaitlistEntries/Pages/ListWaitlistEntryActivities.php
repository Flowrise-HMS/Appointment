<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\WaitlistEntryResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListWaitlistEntryActivities extends ListActivitiesBySubject
{
    protected static string $resource = WaitlistEntryResource::class;
}
