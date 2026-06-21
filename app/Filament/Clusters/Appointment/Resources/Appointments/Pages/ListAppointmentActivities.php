<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListAppointmentActivities extends ListActivities
{
    protected static string $resource = AppointmentResource::class;
}
