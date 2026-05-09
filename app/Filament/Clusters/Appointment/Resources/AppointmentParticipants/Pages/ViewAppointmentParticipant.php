<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;

class ViewAppointmentParticipant extends ViewRecord
{
    protected static string $resource = AppointmentParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
