<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Actions\GenerateSlotsAction;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\AppointmentScheduleResource;
use Modules\Appointment\Models\AppointmentSchedule;

class CreateAppointmentSchedule extends CreateRecord
{
    protected static string $resource = AppointmentScheduleResource::class;

    protected function afterCreate(): void
    {
        /** @var AppointmentSchedule $schedule */
        $schedule = $this->getRecord();

        if ($schedule->is_active) {
            GenerateSlotsAction::run($schedule);
        }
    }
}
