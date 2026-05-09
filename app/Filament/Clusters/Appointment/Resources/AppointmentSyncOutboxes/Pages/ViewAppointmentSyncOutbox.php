<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\AppointmentSyncOutboxResource;

class ViewAppointmentSyncOutbox extends ViewRecord
{
    protected static string $resource = AppointmentSyncOutboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
