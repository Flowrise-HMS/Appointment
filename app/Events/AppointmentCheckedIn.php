<?php

namespace Modules\Appointment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCheckedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly string $appointmentId) {}
}
