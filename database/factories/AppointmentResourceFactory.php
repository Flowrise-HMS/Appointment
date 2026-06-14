<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentResource;
use Modules\Core\Models\Branch;

class AppointmentResourceFactory extends Factory
{
    protected $model = AppointmentResource::class;

    public function definition(): array
    {
        $allocatedFrom = fake()->dateTimeBetween('+1 day', '+15 days');
        $allocatedTo = (clone $allocatedFrom)->modify('+1 hour');

        return [
            'appointment_id' => Appointment::factory(),
            'branch_id' => Branch::factory(),
            'resource_type' => fake()->randomElement(['room', 'equipment', 'bed']),
            'resource_reference' => (string) fake()->uuid(),
            'allocated_from' => $allocatedFrom,
            'allocated_to' => $allocatedTo,
        ];
    }
}
