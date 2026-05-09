<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;
}
