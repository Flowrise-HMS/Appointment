<?php

namespace Modules\Appointment\Classes\Services;

class WaitlistScoringService
{
    public function score(int $urgency, int $waitTime, int $referral, int $manualOverride = 0): int
    {
        $baseScore = ($urgency * 5) + ($waitTime * 3) + ($referral * 2) + $manualOverride;

        return max(0, min(999, $baseScore));
    }
}
