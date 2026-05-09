<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;

class ListAppointmentRecurrenceRules extends ListRecords
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
