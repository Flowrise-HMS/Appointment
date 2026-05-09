<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;

class ListAppointmentParticipants extends ListRecords
{
    protected static string $resource = AppointmentParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
