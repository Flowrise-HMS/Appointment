<?php

namespace Modules\Appointment\Console\Commands;

use Illuminate\Console\Command;
use Modules\Appointment\Classes\Services\SlotGenerationService;
use Modules\Appointment\Models\AppointmentSchedule;

class GenerateAppointmentSlotsCommand extends Command
{
    protected $signature = 'appointments:generate-slots {--schedule= : Only this schedule id}';

    protected $description = 'Generate bookable slots for active appointment schedules over their rolling window.';

    public function handle(SlotGenerationService $generator): int
    {
        $created = 0;
        $removed = 0;

        AppointmentSchedule::query()
            ->withoutGlobalScope('branch')
            ->where('is_active', true)
            ->when($this->option('schedule'), fn ($query, $id) => $query->whereKey($id))
            ->each(function (AppointmentSchedule $schedule) use ($generator, &$created, &$removed): void {
                $result = $generator->generate($schedule);
                $created += $result['created'];
                $removed += $result['removed'];
            });

        $this->info("Slots created: {$created}; stale free slots removed: {$removed}.");

        return self::SUCCESS;
    }
}
