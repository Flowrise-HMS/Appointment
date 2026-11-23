<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\AppointmentScheduleResource;

class ListAppointmentSchedules extends ListRecords
{
    protected static string $resource = AppointmentScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
