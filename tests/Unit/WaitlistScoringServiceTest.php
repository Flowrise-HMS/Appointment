<?php

namespace Modules\Appointment\Tests\Unit;

use Modules\Appointment\Classes\Services\WaitlistScoringService;
use PHPUnit\Framework\TestCase;

class WaitlistScoringServiceTest extends TestCase
{
    public function test_it_calculates_deterministic_score(): void
    {
        $service = new WaitlistScoringService;

        $score = $service->score(5, 4, 2, 3);

        $this->assertSame(44, $score);
    }

    public function test_it_clamps_the_score_range(): void
    {
        $service = new WaitlistScoringService;

        $this->assertSame(0, $service->score(-10, -10, -10, -10));
        $this->assertSame(999, $service->score(100, 100, 100, 100));
    }
}
