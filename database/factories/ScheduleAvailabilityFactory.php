<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\ScheduleAvailability;

/**
 * @extends Factory<ScheduleAvailability>
 */
class ScheduleAvailabilityFactory extends Factory
{
    protected $model = ScheduleAvailability::class;

    public function definition(): array
    {
        return [
            'schedule_id' => AppointmentSchedule::factory(),
            'weekday' => fake()->numberBetween(1, 5),
            'start_time' => '09:00',
            'end_time' => '12:00',
        ];
    }
}
