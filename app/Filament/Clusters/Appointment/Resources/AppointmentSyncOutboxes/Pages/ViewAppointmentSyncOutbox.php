<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\AppointmentSyncOutboxResource;

class ViewAppointmentSyncOutbox extends ViewRecord
{
    protected static string $resource = AppointmentSyncOutboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('Activities')
                ->icon('heroicon-o-bell-alert')
                ->url(fn () => AppointmentSyncOutboxResource::getUrl('activities', ['record' => $this->getRecord()])),
            EditAction::make(),
        ];
    }
}
