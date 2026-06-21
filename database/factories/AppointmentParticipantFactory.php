<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Enums\AppointmentParticipantStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\Core\Models\Branch;

class AppointmentParticipantFactory extends Factory
{
    protected $model = AppointmentParticipant::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'branch_id' => Branch::factory(),
            'participant_type' => fake()->randomElement(['practitioner', 'patient', 'observer']),
            'actor_reference' => fake()->uuid(),
            'required' => fake()->boolean(80),
            'status' => AppointmentParticipantStatus::ACCEPTED,
        ];
    }
}
