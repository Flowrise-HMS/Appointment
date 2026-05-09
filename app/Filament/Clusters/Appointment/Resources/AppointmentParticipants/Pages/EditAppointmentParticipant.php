<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;

class EditAppointmentParticipant extends EditRecord
{
    protected static string $resource = AppointmentParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
