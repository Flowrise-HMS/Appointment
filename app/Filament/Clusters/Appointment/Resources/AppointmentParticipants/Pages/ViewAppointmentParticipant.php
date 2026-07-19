<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;

class ViewAppointmentParticipant extends ViewRecord
{
    protected static string $resource = AppointmentParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('Activities')
                ->icon('heroicon-o-bell-alert')
                ->url(fn () => AppointmentParticipantResource::getUrl('activities', ['record' => $this->getRecord()])),
            EditAction::make(),
        ];
    }
}
