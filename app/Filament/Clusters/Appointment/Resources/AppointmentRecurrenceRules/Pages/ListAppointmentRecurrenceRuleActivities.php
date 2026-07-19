<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListAppointmentRecurrenceRuleActivities extends ListActivitiesBySubject
{
    protected static string $resource = AppointmentRecurrenceRuleResource::class;
}
