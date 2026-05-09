<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\AppointmentSyncOutboxResource;

class ListAppointmentSyncOutboxes extends ListRecords
{
    protected static string $resource = AppointmentSyncOutboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
