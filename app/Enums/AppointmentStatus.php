<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

/**
 * FHIR R4 Appointment.status aligned values (subset used in FlowRise).
 */
enum AppointmentStatus: string implements HasColor, HasDescription, HasLabel
{
    case PROPOSED = 'proposed';
    case PENDING = 'pending';
    case BOOKED = 'booked';
    case ARRIVED = 'arrived';
    case FULFILLED = 'fulfilled';
    case CANCELLED = 'cancelled';
    case NOSHOW = 'noshow';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PROPOSED => 'Proposed',
            self::PENDING => 'Pending',
            self::BOOKED => 'Booked',
            self::ARRIVED => 'Arrived',
            self::FULFILLED => 'Fulfilled',
            self::CANCELLED => 'Cancelled',
            self::NOSHOW => 'No-show',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::PROPOSED => 'A preliminary suggestion that may be modified before confirmation.',
            self::PENDING => 'Awaiting confirmation or external dependency.',
            self::BOOKED => 'All participant types have confirmed; the slot is reserved.',
            self::FULFILLED => 'The visit or service tied to this slot has completed.',
            self::CANCELLED => 'The appointment was cancelled before completion.',
            self::NOSHOW => 'The patient did not attend the scheduled appointment.',
            self::ARRIVED => 'The patient has checked in or arrived for the visit.',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PROPOSED => 'gray',
            self::PENDING => 'warning',
            self::BOOKED => 'primary',
            self::ARRIVED => 'success',
            self::FULFILLED => 'success',
            self::CANCELLED => 'danger',
            self::NOSHOW => 'danger',
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
        return self::BOOKED;
    }

    /**
     * Statuses that still block practitioner scheduling for the same slot window.
     *
     * @return list<self>
     */
    public static function blockingConflictStatuses(): array
    {
        return [
            self::PROPOSED,
            self::PENDING,
            self::BOOKED,
            self::ARRIVED,
        ];
    }

    /**
     * Used when determining if a patient has an upcoming or in-progress visit.
     *
     * @return list<self>
     */
    public static function activePatientAccessStatuses(): array
    {
        return [self::PENDING, self::BOOKED, self::ARRIVED];
    }

    public function allowsCheckIn(): bool
    {
        return in_array($this, [self::BOOKED, self::PENDING], true);
    }

    public function allowsCancellation(): bool
    {
        return ! in_array($this, [self::CANCELLED, self::FULFILLED], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::CANCELLED, self::FULFILLED, self::NOSHOW], true);
    }

    /**
     * @return array{background: string, text: string}
     */
    public function calendarEventColors(): array
    {
        return match ($this) {
            self::PROPOSED => ['background' => '#6b7280', 'text' => '#ffffff'],
            self::PENDING => ['background' => '#f59e0b', 'text' => '#111827'],
            self::BOOKED => ['background' => '#2563eb', 'text' => '#ffffff'],
            self::ARRIVED => ['background' => '#16a34a', 'text' => '#ffffff'],
            self::FULFILLED => ['background' => '#15803d', 'text' => '#ffffff'],
            self::CANCELLED => ['background' => '#dc2626', 'text' => '#ffffff'],
            self::NOSHOW => ['background' => '#b91c1c', 'text' => '#ffffff'],
        };
    }
}
