<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\AppointmentAuditResource;

class CreateAppointmentAudit extends CreateRecord
{
    protected static string $resource = AppointmentAuditResource::class;
}
