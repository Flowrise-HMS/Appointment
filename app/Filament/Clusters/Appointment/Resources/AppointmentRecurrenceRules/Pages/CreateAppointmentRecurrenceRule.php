<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Actions\RecurrenceRuleActions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;
use Modules\Appointment\Models\AppointmentRecurrenceRule;

class CreateAppointmentRecurrenceRule extends CreateRecord
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;

    protected function afterCreate(): void
    {
        /** @var AppointmentRecurrenceRule $rule */
        $rule = $this->getRecord();

        RecurrenceRuleActions::expandAndNotify($rule);
    }
}
