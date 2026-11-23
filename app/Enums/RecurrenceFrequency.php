<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasLabel;

enum RecurrenceFrequency: string implements HasLabel
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAILY => __('Daily'),
            self::WEEKLY => __('Weekly'),
            self::MONTHLY => __('Monthly'),
        };
    }

    /**
     * Weekday codes accepted in a weekly rule's `by_day`, keyed to ISO-8601 day numbers.
     *
     * @return array<string, int>
     */
    public static function weekdayCodes(): array
    {
        return ['MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6, 'SUN' => 7];
    }
}
