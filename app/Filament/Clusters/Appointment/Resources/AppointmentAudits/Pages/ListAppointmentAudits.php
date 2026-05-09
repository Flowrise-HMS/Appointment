<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\AppointmentAuditResource;

class ListAppointmentAudits extends ListRecords
{
    protected static string $resource = AppointmentAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
