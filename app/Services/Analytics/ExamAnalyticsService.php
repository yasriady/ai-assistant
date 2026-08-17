<?php

namespace App\Services\Analytics;

use App\Models\Assessment;
use App\Models\ExamQuestion;
use App\Models\Submission;
use Illuminate\Support\Collection;

class ExamAnalyticsService
{
    /**
     * Compute summary and per-question analytics for an exam assessment.
     *
     * @return array{
     *     assessment_id: int,
     *     submission_count: int,
     *     scored_count: int,
     *     average: float|null,
     *     min: float|null,
     *     max: float|null,
     *     median: float|null,
     *     stddev: float|null,
     *     pass_rate: float|null,
     *     pass_mark: float,
     *     per_question: list<array<string, mixed>>
     * }
     */
    public function analyze(Assessment $assessment, ?float $passMark = null): array
    {
        $assessment->loadMissing([
            'examQuestions.question',
            'submissions.answers',
        ]);

        $maxScore = (float) ($assessment->max_score ?? 100);
        $passMark ??= (float) data_get($assessment->settings, 'pass_mark', $maxScore * 0.5);

        $scores = $assessment->submissions
            ->map(function (Submission $submission) {
                return $submission->final_score ?? $submission->ai_score;
            })
            ->filter(fn ($score) => $score !== null)
            ->map(fn ($score) => (float) $score)
            ->values();

        $stats = $this->descriptive($scores);

        $passRate = null;
        if ($scores->isNotEmpty()) {
            $passed = $scores->filter(fn (float $s) => $s >= $passMark)->count();
            $passRate = round(($passed / $scores->count()) * 100, 2);
        }

        return [
            'assessment_id' => $assessment->id,
            'submission_count' => $assessment->submissions->count(),
            'scored_count' => $scores->count(),
            'average' => $stats['average'],
            'min' => $stats['min'],
            'max' => $stats['max'],
            'median' => $stats['median'],
            'stddev' => $stats['stddev'],
            'pass_rate' => $passRate,
            'pass_mark' => $passMark,
            'per_question' => $this->perQuestionStats($assessment),
        ];
    }

    /**
     * Persist analytics snapshot onto assessment.settings.
     *
     * @return array<string, mixed>
     */
    public function generateAndStore(Assessment $assessment, ?float $passMark = null): array
    {
        $analytics = $this->analyze($assessment, $passMark);
        $settings = $assessment->settings ?? [];
        $settings['analytics'] = array_merge($analytics, [
            'generated_at' => now()->toIso8601String(),
        ]);
        $assessment->update(['settings' => $settings]);

        return $analytics;
    }

    /**
     * @param  Collection<int, float>  $scores
     * @return array{average: float|null, min: float|null, max: float|null, median: float|null, stddev: float|null}
     */
    public function descriptive(Collection $scores): array
    {
        if ($scores->isEmpty()) {
            return [
                'average' => null,
                'min' => null,
                'max' => null,
                'median' => null,
                'stddev' => null,
            ];
        }

        $values = $scores->sort()->values();
        $count = $values->count();
        $average = $values->avg();
        $median = $count % 2 === 1
            ? $values[(int) floor($count / 2)]
            : (($values[$count / 2 - 1] + $values[$count / 2]) / 2);

        $variance = $values->reduce(
            fn (float $carry, float $value) => $carry + (($value - $average) ** 2),
            0.0
        ) / $count;

        return [
            'average' => round((float) $average, 2),
            'min' => round((float) $values->first(), 2),
            'max' => round((float) $values->last(), 2),
            'median' => round((float) $median, 2),
            'stddev' => round(sqrt($variance), 2),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function perQuestionStats(Assessment $assessment): array
    {
        return $assessment->examQuestions->map(function (ExamQuestion $examQuestion) use ($assessment) {
            $questionScores = $assessment->submissions
                ->flatMap(fn (Submission $s) => $s->answers)
                ->filter(fn ($answer) => (int) $answer->exam_question_id === (int) $examQuestion->id
                    || (int) $answer->question_id === (int) $examQuestion->question_id)
                ->map(fn ($answer) => $answer->final_score ?? $answer->ai_score)
                ->filter(fn ($score) => $score !== null)
                ->map(fn ($score) => (float) $score)
                ->values();

            $stats = $this->descriptive($questionScores);
            $maxScore = (float) ($examQuestion->max_score ?? $examQuestion->question?->max_score ?? 0);
            $correctRate = null;

            if ($questionScores->isNotEmpty() && $maxScore > 0) {
                $full = $questionScores->filter(fn (float $s) => abs($s - $maxScore) < 0.001)->count();
                $correctRate = round(($full / $questionScores->count()) * 100, 2);
            }

            return [
                'exam_question_id' => $examQuestion->id,
                'question_id' => $examQuestion->question_id,
                'order_index' => $examQuestion->order_index,
                'question_type' => $examQuestion->question?->question_type?->value,
                'max_score' => $maxScore,
                'response_count' => $questionScores->count(),
                'average' => $stats['average'],
                'min' => $stats['min'],
                'max' => $stats['max'],
                'median' => $stats['median'],
                'stddev' => $stats['stddev'],
                'full_mark_rate' => $correctRate,
            ];
        })->values()->all();
    }
}
