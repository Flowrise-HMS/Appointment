<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Subset of FHIR SlotStatus used by generated slots.
 */
enum SlotStatus: string implements HasColor, HasLabel
{
    case FREE = 'free';
    case BUSY = 'busy';
    case BLOCKED = 'blocked';

    public function getLabel(): string
    {
        return match ($this) {
            self::FREE => __('Free'),
            self::BUSY => __('Booked'),
            self::BLOCKED => __('Blocked'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::FREE => 'success',
            self::BUSY => 'warning',
            self::BLOCKED => 'gray',
        };
    }
}
