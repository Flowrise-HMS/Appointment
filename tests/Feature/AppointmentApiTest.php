<?php

namespace Modules\Appointment\Tests\Feature;

use PHPUnit\Framework\TestCase;

class AppointmentApiTest extends TestCase
{
    public function test_it_exposes_expected_appointment_api_route_templates(): void
    {
        $this->assertSame('/api/v1/appointments', '/api/v1/appointments');
        $this->assertSame('/api/v1/appointments/{appointment}/check-in', '/api/v1/appointments/{appointment}/check-in');
        $this->assertSame('/api/v1/appointments/{appointment}/cancel', '/api/v1/appointments/{appointment}/cancel');
    }

    public function test_it_exposes_waitlist_api_route_templates(): void
    {
        $this->assertSame('/api/v1/waitlist', '/api/v1/waitlist');
        $this->assertSame('/api/v1/waitlist/{waitlistEntry}/offer-slot', '/api/v1/waitlist/{waitlistEntry}/offer-slot');
    }
}
