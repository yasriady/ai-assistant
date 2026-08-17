<?php

namespace App\Services\Assessment;

use App\Enums\SubmissionStatus;
use App\Models\Answer;
use App\Models\FinalAssessment;
use App\Models\Submission;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ReviewService
{
    public function __construct(
        protected AuditLogger $audit,
    ) {}

    /**
     * Accept the AI score as the final score for a submission.
     */
    public function acceptAiScore(Submission $submission, User $reviewer, ?string $reason = null): Submission
    {
        if ($submission->ai_score === null) {
            throw new RuntimeException('Submission has no AI score to accept.');
        }

        return DB::transaction(function () use ($submission, $reviewer, $reason) {
            $old = [
                'final_score' => $submission->final_score,
                'status' => $submission->status?->value,
            ];

            $submission->update([
                'final_score' => $submission->ai_score,
                'status' => SubmissionStatus::Reviewed,
                'reviewed_at' => now(),
            ]);

            $this->audit->log(
                'assessment.accept_ai_score',
                $submission,
                oldValue: $old,
                newValue: [
                    'final_score' => $submission->final_score,
                    'status' => $submission->status->value,
                ],
                reason: $reason,
                userId: $reviewer->id,
            );

            return $submission->fresh();
        });
    }

    /**
     * Manually modify the submission final score.
     */
    public function modifyScore(
        Submission $submission,
        float $score,
        User $reviewer,
        ?string $reason = null,
        ?string $feedback = null,
    ): Submission {
        $max = (float) ($submission->assessment?->max_score ?? $submission->aiAssessments()->whereNull('answer_id')->value('max_score') ?? 100);

        if ($score < 0 || $score > $max + 0.001) {
            throw new InvalidArgumentException("Score must be between 0 and {$max}.");
        }

        return DB::transaction(function () use ($submission, $score, $reviewer, $reason, $feedback, $max) {
            $old = [
                'final_score' => $submission->final_score,
                'status' => $submission->status?->value,
            ];

            $submission->loadMissing('assessment');

            $submission->update([
                'final_score' => $score,
                'status' => SubmissionStatus::Reviewed,
                'reviewed_at' => now(),
            ]);

            $this->audit->log(
                'assessment.modify_score',
                $submission,
                oldValue: $old,
                newValue: [
                    'final_score' => $score,
                    'max_score' => $max,
                    'status' => $submission->status->value,
                    'feedback' => $feedback,
                ],
                reason: $reason,
                userId: $reviewer->id,
            );

            return $submission->fresh();
        });
    }

    /**
     * Accept AI score for a single exam answer.
     */
    public function acceptAnswerAiScore(Answer $answer, User $reviewer, ?string $reason = null): Answer
    {
        if ($answer->ai_score === null) {
            throw new RuntimeException('Answer has no AI score to accept.');
        }

        return DB::transaction(function () use ($answer, $reviewer, $reason) {
            $old = ['final_score' => $answer->final_score];

            $answer->update(['final_score' => $answer->ai_score]);

            $this->recalculateSubmissionFromAnswers($answer->submission);

            $this->audit->log(
                'answer.accept_ai_score',
                $answer,
                oldValue: $old,
                newValue: ['final_score' => $answer->final_score],
                reason: $reason,
                userId: $reviewer->id,
            );

            return $answer->fresh();
        });
    }

    /**
     * Modify a single answer final score.
     */
    public function modifyAnswerScore(
        Answer $answer,
        float $score,
        User $reviewer,
        ?string $reason = null,
    ): Answer {
        $max = (float) ($answer->max_score ?? $answer->question?->max_score ?? 0);

        if ($score < 0 || ($max > 0 && $score > $max + 0.001)) {
            throw new InvalidArgumentException("Answer score must be between 0 and {$max}.");
        }

        return DB::transaction(function () use ($answer, $score, $reviewer, $reason) {
            $old = ['final_score' => $answer->final_score];

            $answer->update(['final_score' => $score]);
            $this->recalculateSubmissionFromAnswers($answer->submission);

            $this->audit->log(
                'answer.modify_score',
                $answer,
                oldValue: $old,
                newValue: ['final_score' => $score],
                reason: $reason,
                userId: $reviewer->id,
            );

            return $answer->fresh();
        });
    }

    /**
     * Finalize a submission after review.
     */
    public function finalize(
        Submission $submission,
        User $reviewer,
        ?string $lecturerNotes = null,
        ?string $feedback = null,
    ): FinalAssessment {
        $score = $submission->final_score ?? $submission->ai_score;

        if ($score === null) {
            throw new RuntimeException('Cannot finalize submission without a score.');
        }

        return DB::transaction(function () use ($submission, $reviewer, $lecturerNotes, $feedback, $score) {
            $old = [
                'status' => $submission->status?->value,
                'final_score' => $submission->final_score,
            ];

            $maxScore = (float) (
                $submission->assessment?->max_score
                ?? $submission->aiAssessments()->whereNull('answer_id')->value('max_score')
                ?? 100
            );

            $aiAssessmentId = $submission->aiAssessments()
                ->whereNull('answer_id')
                ->latest('id')
                ->value('id');

            $final = FinalAssessment::query()->updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'answer_id' => null,
                    'ai_assessment_id' => $aiAssessmentId,
                    'score' => $score,
                    'max_score' => $maxScore,
                    'feedback' => $feedback,
                    'lecturer_notes' => $lecturerNotes,
                    'finalized_by' => $reviewer->id,
                    'finalized_at' => now(),
                ]
            );

            $submission->update([
                'final_score' => $score,
                'status' => SubmissionStatus::Finalized,
                'finalized_at' => now(),
                'reviewed_at' => $submission->reviewed_at ?? now(),
            ]);

            $this->audit->log(
                'assessment.finalize',
                $submission,
                oldValue: $old,
                newValue: [
                    'status' => SubmissionStatus::Finalized->value,
                    'final_score' => $score,
                    'final_assessment_id' => $final->id,
                ],
                reason: $lecturerNotes,
                userId: $reviewer->id,
            );

            return $final->fresh();
        });
    }

    protected function recalculateSubmissionFromAnswers(?Submission $submission): void
    {
        if (! $submission) {
            return;
        }

        $sum = (float) $submission->answers()->sum('final_score');

        $submission->update([
            'final_score' => $sum,
            'status' => SubmissionStatus::Reviewed,
            'reviewed_at' => now(),
        ]);
    }
}
