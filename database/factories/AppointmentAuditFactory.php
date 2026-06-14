<?php

namespace Modules\Appointment\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentAudit;
use Modules\Core\Models\Branch;

class AppointmentAuditFactory extends Factory
{
    protected $model = AppointmentAudit::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'branch_id' => Branch::factory(),
            'actor_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'cancelled', 'checked_in']),
            'before_payload' => ['status' => 'booked'],
            'after_payload' => ['status' => 'checked_in'],
            'ip_address' => fake()->ipv4(),
            'occurred_at' => now(),
        ];
    }
}
