<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\AppointmentAuditResource;

class ViewAppointmentAudit extends ViewRecord
{
    protected static string $resource = AppointmentAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
