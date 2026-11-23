<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Actions\RecurrenceRuleActions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;
use Modules\Appointment\Models\AppointmentRecurrenceRule;

class EditAppointmentRecurrenceRule extends EditRecord
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            RecurrenceRuleActions::cancelSeries(),
            DeleteAction::make(),
        ];
    }

    /**
     * Extending a rule (more occurrences, later end date) books the new
     * instances; already-booked ones are left untouched.
     */
    protected function afterSave(): void
    {
        /** @var AppointmentRecurrenceRule $rule */
        $rule = $this->getRecord();

        RecurrenceRuleActions::expandAndNotify($rule);
    }
}
