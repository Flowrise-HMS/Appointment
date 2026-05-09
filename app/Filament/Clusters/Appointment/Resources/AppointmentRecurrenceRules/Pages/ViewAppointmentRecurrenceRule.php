<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;

class ViewAppointmentRecurrenceRule extends ViewRecord
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
