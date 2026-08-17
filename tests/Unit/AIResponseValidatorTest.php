<?php

namespace Tests\Unit;

use App\Services\AI\AIResponseValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIResponseValidatorTest extends TestCase
{
    #[Test]
    public function it_validates_a_complete_payload(): void
    {
        $payload = [
            'score' => 8,
            'max_score' => 10,
            'criteria' => [
                [
                    'name' => 'Clarity',
                    'score' => 8,
                    'max_score' => 10,
                    'evidence' => 'Quote',
                    'reasoning' => 'Clear',
                    'feedback' => 'Good',
                ],
            ],
            'overall_feedback' => 'Solid work.',
            'confidence' => 0.81,
        ];

        $result = (new AIResponseValidator)->validate($payload);

        $this->assertSame(8.0, $result['score']);
        $this->assertSame(10.0, $result['max_score']);
        $this->assertSame('Clarity', $result['criteria'][0]['name']);
        $this->assertSame(0.81, $result['confidence']);
    }

    #[Test]
    public function it_rejects_missing_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required assessment field');

        (new AIResponseValidator)->validate([
            'score' => 1,
            'max_score' => 10,
        ]);
    }

    #[Test]
    public function it_rejects_score_above_max(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot exceed max_score');

        (new AIResponseValidator)->validate([
            'score' => 12,
            'max_score' => 10,
            'criteria' => [['name' => 'A', 'score' => 1, 'max_score' => 1]],
            'overall_feedback' => 'ok',
            'confidence' => 0.5,
        ]);
    }

    #[Test]
    public function it_rejects_invalid_confidence(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('confidence must be between 0 and 1');

        (new AIResponseValidator)->validate([
            'score' => 1,
            'max_score' => 10,
            'criteria' => [['name' => 'A', 'score' => 1, 'max_score' => 1]],
            'overall_feedback' => 'ok',
            'confidence' => 1.5,
        ]);
    }

    #[Test]
    public function repair_hints_are_non_empty(): void
    {
        $hints = (new AIResponseValidator)->repairHints();

        $this->assertNotEmpty($hints);
        $this->assertStringContainsString('JSON', $hints[0]);
    }
}
