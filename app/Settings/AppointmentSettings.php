<?php

namespace Modules\Appointment\Settings;

use Spatie\LaravelSettings\Settings;

class AppointmentSettings extends Settings
{
    public int $default_duration_minutes = 5;

    public string $default_status = 'booked';

    public string $default_type = 'outpatient';

    public bool $waitlist_enabled = true;

    public bool $telehealth_enabled = true;

    public bool $recurrence_enabled = true;

    public bool $external_sync_enabled = true;

    public bool $waitlist_api_enabled = true;

    public int $calendar_first_day_of_week = 1;

    public static function group(): string
    {
        return 'appointment';
    }
}
