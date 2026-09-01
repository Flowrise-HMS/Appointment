<?php

namespace Modules\Appointment\Classes\Services;

use Modules\Appointment\Models\WaitlistEntry;

class WaitlistScoringService
{
    public function score(int $urgency, int $waitTime, int $referral, int $manualOverride = 0): int
    {
        $baseScore = ($urgency * 5) + ($waitTime * 3) + ($referral * 2) + $manualOverride;

        return max(0, min(999, $baseScore));
    }

    /**
     * Create a waitlist entry with its priority score already computed.
     *
     * The API controller previously called `WaitlistEntry::create()` directly, which
     * both bypassed the service layer and left scoring as something every caller had
     * to remember to do first.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createEntry(array $attributes): WaitlistEntry
    {
        $attributes['computed_priority_score'] = $this->score(
            (int) $attributes['urgency_score'],
            (int) $attributes['wait_time_score'],
            (int) $attributes['referral_score'],
            (int) ($attributes['manual_override_score'] ?? 0),
        );

        return WaitlistEntry::create($attributes);
    }
}
