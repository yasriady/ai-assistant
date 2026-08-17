<?php

namespace Tests\Unit;

use App\Services\Assessment\RubricScoringService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RubricScoringServiceTest extends TestCase
{
    #[Test]
    public function it_returns_zeros_for_empty_criteria(): void
    {
        $result = (new RubricScoringService)->calculate([], 100);

        $this->assertSame(0.0, $result['score']);
        $this->assertSame(100.0, $result['max_score']);
        $this->assertSame(0.0, $result['percentage']);
        $this->assertSame([], $result['criteria']);
    }

    #[Test]
    public function it_sums_raw_scores_when_weights_absent(): void
    {
        $result = (new RubricScoringService)->calculate([
            ['name' => 'A', 'score' => 8, 'max_score' => 10],
            ['name' => 'B', 'score' => 15, 'max_score' => 20],
        ]);

        $this->assertSame(23.0, $result['score']);
        $this->assertSame(30.0, $result['max_score']);
        $this->assertEqualsWithDelta(76.67, $result['percentage'], 0.01);
    }

    #[Test]
    public function it_applies_weighted_scoring(): void
    {
        $result = (new RubricScoringService)->calculate([
            ['name' => 'Intro', 'score' => 10, 'max_score' => 10, 'weight' => 40],
            ['name' => 'Body', 'score' => 5, 'max_score' => 10, 'weight' => 60],
        ], 100);

        // (1.0*40 + 0.5*60) / 100 = 0.7 → 70
        $this->assertSame(70.0, $result['score']);
        $this->assertSame(100.0, $result['max_score']);
        $this->assertSame(70.0, $result['percentage']);
    }

    #[Test]
    public function it_clamps_scores_to_max(): void
    {
        $result = (new RubricScoringService)->calculate([
            ['name' => 'A', 'score' => 15, 'max_score' => 10],
        ]);

        $this->assertSame(10.0, $result['score']);
        $this->assertSame(10.0, $result['criteria'][0]['score']);
    }
}
