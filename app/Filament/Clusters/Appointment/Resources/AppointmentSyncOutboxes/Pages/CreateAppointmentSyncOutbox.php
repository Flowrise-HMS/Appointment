<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\AppointmentSyncOutboxResource;

class CreateAppointmentSyncOutbox extends CreateRecord
{
    protected static string $resource = AppointmentSyncOutboxResource::class;
}
