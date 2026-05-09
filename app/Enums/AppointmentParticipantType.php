<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

/**
 * High-level participant actor categories for scheduling rows.
 */
enum AppointmentParticipantType: string implements HasColor, HasDescription, HasLabel
{
    case PATIENT = 'patient';
    case PRACTITIONER = 'practitioner';
    case RELATED_PERSON = 'related_person';
    case DEVICE = 'device';
    case LOCATION = 'location';
    case GROUP = 'group';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PATIENT => 'Patient',
            self::PRACTITIONER => 'Practitioner',
            self::RELATED_PERSON => 'Related person',
            self::DEVICE => 'Device',
            self::LOCATION => 'Location',
            self::GROUP => 'Group',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::PATIENT => 'The subject of care for the appointment.',
            self::PRACTITIONER => 'Licensed or credentialed clinician.',
            self::RELATED_PERSON => 'Family member, caregiver, or escort.',
            self::DEVICE => 'Equipment or monitoring asset.',
            self::LOCATION => 'Room, ward, or facility resource.',
            self::GROUP => 'Team or pooled coverage.',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PATIENT => 'primary',
            self::PRACTITIONER => 'info',
            self::RELATED_PERSON => 'gray',
            self::DEVICE => 'secondary',
            self::LOCATION => 'warning',
            self::GROUP => 'success',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function default(): self
    {
        return self::PRACTITIONER;
    }
}
