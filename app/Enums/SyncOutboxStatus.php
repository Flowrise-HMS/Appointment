<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SyncOutboxStatus: string implements HasColor, HasDescription, HasLabel
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::PENDING => 'Message is queued and eligible for dispatch.',
            self::PROCESSING => 'Worker has claimed the message.',
            self::COMPLETED => 'Downstream system acknowledged delivery.',
            self::FAILED => 'Delivery failed after retries; inspect last_error.',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PROCESSING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
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
        return self::PENDING;
    }
}
