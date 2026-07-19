<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\AppointmentAuditResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListAppointmentAuditActivities extends ListActivitiesBySubject
{
    protected static string $resource = AppointmentAuditResource::class;
}
