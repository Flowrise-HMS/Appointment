<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('+1 day', '+15 days');
        $endAt = (clone $startAt)->modify('+30 minutes');

        return [
            'branch_id' => Branch::factory(),
            'patient_id' => Patient::factory(),
            'status' => AppointmentStatus::BOOKED,
            'priority' => fake()->numberBetween(1, 9),
            'appointment_type' => AppointmentType::OUTPATIENT,
            'reason_text' => fake()->sentence(),
            'start_at' => $startAt,
            'end_at' => $endAt,
        ];
    }
}
