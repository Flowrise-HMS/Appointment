<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum WaitlistEntryStatus: string implements HasColor, HasDescription, HasLabel
{
    case WAITING = 'waiting';
    case OFFERED = 'offered';
    case BOOKED = 'booked';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::WAITING => 'Waiting',
            self::OFFERED => 'Slot offered',
            self::BOOKED => 'Booked from waitlist',
            self::DECLINED => 'Declined offer',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::WAITING => 'Patient is queued for the next matching slot.',
            self::OFFERED => 'A concrete slot was proposed to the patient.',
            self::BOOKED => 'Waitlist entry converted to a live appointment.',
            self::DECLINED => 'Patient or staff declined the offered slot.',
            self::EXPIRED => 'Offer timed out without response.',
            self::CANCELLED => 'Entry removed before booking.',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::WAITING => 'warning',
            self::OFFERED => 'info',
            self::BOOKED => 'success',
            self::DECLINED => 'gray',
            self::EXPIRED => 'danger',
            self::CANCELLED => 'danger',
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
        return self::WAITING;
    }
}
