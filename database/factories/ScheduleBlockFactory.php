<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Core\Models\Branch;

class ScheduleBlockFactory extends Factory
{
    protected $model = ScheduleBlock::class;

    public function definition(): array
    {
        $blockedFrom = fake()->dateTimeBetween('+1 day', '+15 days');
        $blockedTo = (clone $blockedFrom)->modify('+4 hours');

        return [
            'branch_id' => Branch::factory(),
            'reason' => fake()->sentence(),
            'blocked_from' => $blockedFrom,
            'blocked_to' => $blockedTo,
        ];
    }
}
