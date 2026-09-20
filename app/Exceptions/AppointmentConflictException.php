<?php

namespace Modules\Appointment\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Raised when a practitioner already has an appointment or schedule block in
 * the requested slot. It is an HTTP 422 for API callers and is converted into
 * a form validation error by the Filament pages.
 */
class AppointmentConflictException extends HttpException
{
    public function __construct(string $message = 'Practitioner has a scheduling conflict for the selected slot.')
    {
        parent::__construct(422, $message);
    }
}
