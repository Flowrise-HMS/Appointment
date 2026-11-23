<?php

namespace Modules\Appointment\Exceptions;

/**
 * The practitioner has a schedule for that day but is not available for the
 * whole requested window. A conflict for every caller that already handles
 * {@see AppointmentConflictException} (HTTP 422, Filament notification).
 */
class OutsideScheduleException extends AppointmentConflictException
{
    public function __construct(string $message = 'The practitioner is not scheduled to see patients for the whole selected time.')
    {
        parent::__construct($message);
    }
}
