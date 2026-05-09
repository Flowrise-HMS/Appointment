<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;

class CreateAppointmentParticipant extends CreateRecord
{
    protected static string $resource = AppointmentParticipantResource::class;
}
