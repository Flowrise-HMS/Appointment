<?php

namespace Modules\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Enums\SyncOutboxStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSyncOutbox;
use Modules\Core\Models\Branch;

class AppointmentSyncOutboxFactory extends Factory
{
    protected $model = AppointmentSyncOutbox::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'aggregate_type' => AppointmentSyncOutbox::AGGREGATE_TYPE_APPOINTMENT,
            'aggregate_id' => Appointment::factory(),
            'event_name' => fake()->randomElement(['appointment.created', 'appointment.updated', 'appointment.cancelled']),
            'payload' => ['test' => true],
            'status' => SyncOutboxStatus::PENDING,
            'available_at' => now(),
        ];
    }
}
