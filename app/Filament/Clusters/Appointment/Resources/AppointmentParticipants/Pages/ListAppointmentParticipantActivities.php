<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListAppointmentParticipantActivities extends ListActivitiesBySubject
{
    protected static string $resource = AppointmentParticipantResource::class;
}
