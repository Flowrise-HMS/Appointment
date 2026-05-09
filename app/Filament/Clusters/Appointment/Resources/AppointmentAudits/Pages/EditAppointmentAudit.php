<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\AppointmentAuditResource;

class EditAppointmentAudit extends EditRecord
{
    protected static string $resource = AppointmentAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
