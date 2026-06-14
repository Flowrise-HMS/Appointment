<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Core\Models\Branch;

class AppointmentRecurrenceRuleFactory extends Factory
{
    protected $model = AppointmentRecurrenceRule::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'branch_id' => Branch::factory(),
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
            'interval' => fake()->numberBetween(1, 4),
            'by_day' => fake()->randomElement(['MON,WED,FRI', 'TUE,THU']),
            'occurrence_count' => fake()->numberBetween(2, 12),
            'timezone' => 'Africa/Accra',
        ];
    }
}
