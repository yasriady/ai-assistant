<?php

namespace App\Services\Assessment;

class RubricScoringService
{
    /**
     * Calculate a weighted total from criterion scores.
     *
     * Each criterion item may include: name, score, max_score, weight.
     * When weights are present, scores are normalized then weighted.
     * When absent, raw criterion scores are summed.
     *
     * @param  list<array<string, mixed>>  $criteria
     * @return array{
     *     score: float,
     *     max_score: float,
     *     percentage: float,
     *     criteria: list<array<string, mixed>>
     * }
     */
    public function calculate(array $criteria, ?float $targetMaxScore = null): array
    {
        if ($criteria === []) {
            return [
                'score' => 0.0,
                'max_score' => $targetMaxScore ?? 0.0,
                'percentage' => 0.0,
                'criteria' => [],
            ];
        }

        $hasWeights = false;
        foreach ($criteria as $item) {
            if (isset($item['weight']) && is_numeric($item['weight']) && (float) $item['weight'] > 0) {
                $hasWeights = true;
                break;
            }
        }

        $normalized = [];
        $rawScore = 0.0;
        $rawMax = 0.0;
        $weightSum = 0.0;
        $weightedRatioSum = 0.0;

        foreach (array_values($criteria) as $index => $item) {
            $max = (float) ($item['max_score'] ?? 0);
            $score = (float) ($item['score'] ?? 0);
            $score = max(0, min($score, $max > 0 ? $max : $score));
            $weight = (float) ($item['weight'] ?? 0);
            $ratio = $max > 0 ? ($score / $max) : 0.0;

            if ($hasWeights && $weight > 0) {
                $weightSum += $weight;
                $weightedRatioSum += $ratio * $weight;
            }

            $rawScore += $score;
            $rawMax += $max;

            $normalized[] = [
                'name' => (string) ($item['name'] ?? 'Criterion '.($index + 1)),
                'score' => round($score, 2),
                'max_score' => round($max, 2),
                'weight' => round($weight, 2),
                'ratio' => round($ratio, 4),
                'evidence' => $item['evidence'] ?? null,
                'feedback' => $item['feedback'] ?? null,
                'reasoning' => $item['reasoning'] ?? null,
                'insufficient_evidence' => (bool) ($item['insufficient_evidence'] ?? false),
            ];
        }

        if ($hasWeights && $weightSum > 0) {
            $percentage = ($weightedRatioSum / $weightSum) * 100;
            $maxScore = $targetMaxScore ?? ($rawMax > 0 ? $rawMax : 100.0);
            $totalScore = ($percentage / 100) * $maxScore;
        } else {
            $maxScore = $targetMaxScore ?? ($rawMax > 0 ? $rawMax : 0.0);
            if ($targetMaxScore !== null && $rawMax > 0 && abs($rawMax - $targetMaxScore) > 0.001) {
                $totalScore = ($rawScore / $rawMax) * $targetMaxScore;
            } else {
                $totalScore = $rawScore;
                $maxScore = $rawMax > 0 ? $rawMax : $maxScore;
            }
            $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0.0;
        }

        return [
            'score' => round($totalScore, 2),
            'max_score' => round($maxScore, 2),
            'percentage' => round($percentage, 2),
            'criteria' => $normalized,
        ];
    }
}
