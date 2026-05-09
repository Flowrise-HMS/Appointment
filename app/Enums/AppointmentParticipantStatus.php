<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

/**
 * FHIR Appointment.participant.status values in use.
 */
enum AppointmentParticipantStatus: string implements HasColor, HasDescription, HasLabel
{
    case NEEDS_ACTION = 'needs-action';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case TENTATIVE = 'tentative';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::NEEDS_ACTION => 'Needs action',
            self::ACCEPTED => 'Accepted',
            self::DECLINED => 'Declined',
            self::TENTATIVE => 'Tentative',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::NEEDS_ACTION => 'Participant has not yet responded to the invitation.',
            self::ACCEPTED => 'Participant has agreed to attend.',
            self::DECLINED => 'Participant cannot attend this slot.',
            self::TENTATIVE => 'Participant may attend; confirmation pending.',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NEEDS_ACTION => 'warning',
            self::ACCEPTED => 'success',
            self::DECLINED => 'danger',
            self::TENTATIVE => 'gray',
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
        return self::NEEDS_ACTION;
    }
}
