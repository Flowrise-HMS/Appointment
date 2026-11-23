<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Actions\GenerateSlotsAction;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\AppointmentScheduleResource;

class ViewAppointmentSchedule extends ViewRecord
{
    protected static string $resource = AppointmentScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            GenerateSlotsAction::make(),
            EditAction::make(),
        ];
    }
}
