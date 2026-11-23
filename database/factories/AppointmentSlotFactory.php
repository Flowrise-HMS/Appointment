<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\AppointmentSlot;

/**
 * @extends Factory<AppointmentSlot>
 */
class AppointmentSlotFactory extends Factory
{
    protected $model = AppointmentSlot::class;

    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 10))->startOfHour();

        return [
            'schedule_id' => AppointmentSchedule::factory(),
            'branch_id' => fn (array $attributes) => AppointmentSchedule::query()->withoutGlobalScope('branch')->find($attributes['schedule_id'])?->branch_id,
            'start_at' => $start,
            'end_at' => $start->copy()->addMinutes(30),
            'status' => SlotStatus::FREE,
        ];
    }
}
