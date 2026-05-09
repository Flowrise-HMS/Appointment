<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;

class CreateAppointmentRecurrenceRule extends CreateRecord
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;
}
