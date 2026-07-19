<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListAppointmentActivities extends ListActivitiesBySubject
{
    protected static string $resource = AppointmentResource::class;
}
