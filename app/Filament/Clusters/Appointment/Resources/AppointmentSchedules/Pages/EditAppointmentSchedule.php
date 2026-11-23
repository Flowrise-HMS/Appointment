<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Actions\GenerateSlotsAction;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\AppointmentScheduleResource;
use Modules\Appointment\Models\AppointmentSchedule;

class EditAppointmentSchedule extends EditRecord
{
    protected static string $resource = AppointmentScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Changed hours or slot length take effect at once: new slots are
     * generated and free slots that no longer fit are removed.
     */
    protected function afterSave(): void
    {
        /** @var AppointmentSchedule $schedule */
        $schedule = $this->getRecord();

        if ($schedule->is_active) {
            GenerateSlotsAction::run($schedule);
        }
    }
}
