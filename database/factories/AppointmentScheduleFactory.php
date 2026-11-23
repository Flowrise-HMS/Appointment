<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\ScheduleAvailability;
use Modules\Core\Models\Branch;

/**
 * @extends Factory<AppointmentSchedule>
 */
class AppointmentScheduleFactory extends Factory
{
    protected $model = AppointmentSchedule::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->words(2, true).' clinic',
            'slot_minutes' => 30,
            'generation_days' => 14,
            'timezone' => 'UTC',
            'is_active' => true,
        ];
    }

    /**
     * Weekday availability (Mon–Fri) between the given local times.
     */
    public function weekdays(string $from = '09:00', string $to = '12:00'): static
    {
        return $this->afterCreating(function (AppointmentSchedule $schedule) use ($from, $to): void {
            foreach (range(1, 5) as $weekday) {
                ScheduleAvailability::create([
                    'schedule_id' => $schedule->id,
                    'weekday' => $weekday,
                    'start_time' => $from,
                    'end_time' => $to,
                ]);
            }
        });
    }
}
