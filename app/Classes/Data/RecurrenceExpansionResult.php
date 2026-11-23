<?php

namespace Modules\Appointment\Classes\Data;

use Illuminate\Support\Collection;
use Modules\Appointment\Models\Appointment;

/**
 * Outcome of expanding one recurrence rule.
 */
final class RecurrenceExpansionResult
{
    /**
     * @param  Collection<int, Appointment>  $created
     * @param  list<string>  $existing  local dates (Y-m-d) whose instance already existed
     * @param  list<string>  $conflicts  local dates (Y-m-d) skipped because the practitioner was busy
     */
    public function __construct(
        public readonly Collection $created,
        public readonly array $existing = [],
        public readonly array $conflicts = [],
    ) {}

    public static function empty(): self
    {
        return new self(collect());
    }

    public function summary(): string
    {
        $parts = [trans_choice(':count occurrence created|:count occurrences created', $this->created->count(), ['count' => $this->created->count()])];

        if ($this->existing !== []) {
            $parts[] = __(':count already scheduled', ['count' => count($this->existing)]);
        }

        if ($this->conflicts !== []) {
            $parts[] = __(':count skipped for conflicts (:dates)', [
                'count' => count($this->conflicts),
                'dates' => implode(', ', $this->conflicts),
            ]);
        }

        return implode('; ', $parts).'.';
    }
}
