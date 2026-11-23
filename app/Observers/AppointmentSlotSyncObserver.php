<?php

namespace Modules\Appointment\Observers;

use Illuminate\Database\Eloquent\Model;
use Modules\Appointment\Classes\Services\SlotStatusService;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSlot;
use Modules\Appointment\Models\ScheduleBlock;

/**
 * Keeps generated slot statuses in step with appointments and schedule blocks
 * however they are written (service, Filament, API, recurrence expansion).
 */
class AppointmentSlotSyncObserver
{
    public function __construct(protected SlotStatusService $statuses) {}

    public function saved(Model $model): void
    {
        $this->sync($model);
    }

    public function deleted(Model $model): void
    {
        $this->sync($model);
    }

    public function restored(Model $model): void
    {
        $this->sync($model);
    }

    private function sync(Model $model): void
    {
        // Nothing to do until a schedule has generated slots.
        if (! AppointmentSlot::query()->withoutGlobalScope('branch')->exists()) {
            return;
        }

        match (true) {
            $model instanceof Appointment => $this->statuses->refreshForAppointment($model),
            $model instanceof ScheduleBlock => $this->statuses->refreshForBlock($model),
            default => null,
        };
    }
}
