<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
