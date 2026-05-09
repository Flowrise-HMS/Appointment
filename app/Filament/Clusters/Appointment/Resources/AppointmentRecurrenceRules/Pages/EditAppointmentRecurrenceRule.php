<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;

class EditAppointmentRecurrenceRule extends EditRecord
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
