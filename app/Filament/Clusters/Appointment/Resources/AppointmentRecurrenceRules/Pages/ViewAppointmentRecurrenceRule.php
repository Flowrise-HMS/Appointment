<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;

class ViewAppointmentRecurrenceRule extends ViewRecord
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('Activities')
                ->icon('heroicon-o-bell-alert')
                ->url(fn () => AppointmentRecurrenceRuleResource::getUrl('activities', ['record' => $this->getRecord()])),
            EditAction::make(),
        ];
    }
}
