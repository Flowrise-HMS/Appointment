<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use Modules\Core\Support\SuperAdmin;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('activities')
                ->visible(fn (): bool => SuperAdmin::check())
                ->label('Activities')
                ->icon('heroicon-o-bell-alert')
                ->url(fn () => AppointmentResource::getUrl('activities', ['record' => $this->getRecord()])),
        ];
    }
}
