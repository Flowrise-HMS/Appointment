<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\AppointmentSyncOutboxResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListAppointmentSyncOutboxActivities extends ListActivitiesBySubject
{
    protected static string $resource = AppointmentSyncOutboxResource::class;
}
