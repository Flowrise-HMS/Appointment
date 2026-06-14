<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Enums\WaitlistEntryStatus;
use Modules\Appointment\Models\WaitlistEntry;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;

class WaitlistEntryFactory extends Factory
{
    protected $model = WaitlistEntry::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'patient_id' => Patient::factory(),
            'urgency_score' => fake()->numberBetween(0, 100),
            'wait_time_score' => fake()->numberBetween(0, 100),
            'referral_score' => fake()->numberBetween(0, 100),
            'computed_priority_score' => fake()->numberBetween(0, 300),
            'status' => WaitlistEntryStatus::ACTIVE,
        ];
    }
}
