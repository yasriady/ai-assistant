<?php

namespace App\Services\Assessment;

use App\Enums\JobProcessStatus;
use App\Enums\SubmissionStatus;
use App\Models\AiAssessment;
use App\Models\AiAssessmentItem;
use App\Models\Answer;
use App\Models\Submission;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExamAssessmentService
{
    public function __construct(
        protected AIManager $ai,
        protected DeterministicGradingService $deterministic,
    ) {}

    /**
     * Grade all answers for a submission.
     *
     * @return list<AiAssessment|array<string, mixed>>
     */
    public function assessSubmission(Submission $submission): array
    {
        $submission->loadMissing(['answers.question.options', 'answers.question.rubric.criteria', 'assessment']);
        $results = [];

        foreach ($submission->answers as $answer) {
            $results[] = $this->assessAnswer($answer);
        }

        $total = (float) $submission->answers()->sum('ai_score');
        $submission->update([
            'ai_score' => $total,
            'status' => SubmissionStatus::Assessed,
            'processed_at' => now(),
            'failure_reason' => null,
        ]);

        return $results;
    }

    /**
     * Grade a single answer (deterministic for MCQ/TF, AI otherwise).
     *
     * @return AiAssessment|array<string, mixed>
     */
    public function assessAnswer(Answer $answer): AiAssessment|array
    {
        $answer->loadMissing(['question.options', 'question.rubric.criteria.levels', 'submission.assessment', 'examQuestion']);

        $question = $answer->question;
        if (! $question) {
            throw new RuntimeException("Answer #{$answer->id} has no question.");
        }

        $maxScore = (float) (
            $answer->max_score
            ?? $answer->examQuestion?->max_score
            ?? $question->max_score
            ?? 1
        );

        if ($answer->max_score === null) {
            $answer->update(['max_score' => $maxScore]);
        }

        if ($this->deterministic->supports($question->question_type)) {
            $graded = $this->deterministic->grade($answer, $question);

            $answer->update([
                'ai_score' => $graded['score'],
                'final_score' => $graded['score'],
            ]);

            return $graded;
        }

        return $this->assessWithAi($answer, $maxScore);
    }

    protected function assessWithAi(Answer $answer, float $maxScore): AiAssessment
    {
        $question = $answer->question;
        $submission = $answer->submission;
        $assessment = $submission?->assessment;
        $rubric = $question?->rubric;

        $aiAssessment = $this->findOrCreateAiAssessment($answer);

        $aiAssessment->update([
            'status' => JobProcessStatus::Processing,
            'error_message' => null,
            'provider' => (string) config('ai.provider', 'null'),
            'model' => (string) (config('ai.model') ?: config('ai.providers.'.config('ai.provider').'.model') ?: 'unknown'),
            'prompt_version' => (string) config('ai.prompt_version', '1.0'),
            'rubric_version' => (string) ($rubric?->version ?? $assessment?->rubric_version ?? ''),
            'assessment_version' => (string) ($assessment?->id ?? ''),
        ]);

        try {
            $payload = [
                'question_text' => $question->question_text,
                'question_type' => $question->question_type->value,
                'expected_answer' => $question->expected_answer,
                'key_concepts' => $question->key_concepts ?? [],
                'answer_text' => $answer->answer_text ?? '',
                'max_score' => $maxScore,
                'rubric' => $rubric ? [
                    'name' => $rubric->name,
                    'criteria' => $rubric->criteria->map(fn ($c) => [
                        'name' => $c->name,
                        'description' => $c->description,
                        'weight' => (float) $c->weight,
                        'max_score' => (float) $c->max_score,
                    ])->all(),
                ] : [],
            ];

            $result = $this->ai->assessAnswer($payload, [
                'user_id' => $assessment?->user_id,
                'assessment_id' => $assessment?->id,
                'submission_id' => $submission?->id,
                'method' => 'assessAnswer',
            ]);

            $score = (float) ($result['score'] ?? 0);

            DB::transaction(function () use ($aiAssessment, $result, $score, $maxScore, $answer): void {
                $aiAssessment->items()->delete();

                foreach (array_values($result['criteria'] ?? []) as $index => $item) {
                    AiAssessmentItem::query()->create([
                        'ai_assessment_id' => $aiAssessment->id,
                        'criterion_name' => (string) ($item['name'] ?? 'Criterion'),
                        'score' => $item['score'] ?? null,
                        'max_score' => $item['max_score'] ?? null,
                        'evidence' => $item['evidence'] ?? null,
                        'reasoning' => $item['reasoning'] ?? null,
                        'feedback' => $item['feedback'] ?? null,
                        'insufficient_evidence' => (bool) ($item['insufficient_evidence'] ?? false),
                        'order_index' => $index,
                    ]);
                }

                $aiAssessment->update([
                    'status' => JobProcessStatus::Completed,
                    'score' => $score,
                    'max_score' => $maxScore,
                    'confidence' => $result['confidence'] ?? null,
                    'overall_feedback' => $result['overall_feedback'] ?? null,
                    'raw_response' => $result['_raw'] ?? $result,
                    'structured_result' => [
                        'score' => $score,
                        'max_score' => $maxScore,
                        'criteria' => $result['criteria'] ?? [],
                        'overall_feedback' => $result['overall_feedback'] ?? null,
                        'confidence' => $result['confidence'] ?? null,
                    ],
                    'provider' => (string) ($result['_provider'] ?? $aiAssessment->provider),
                    'model' => (string) ($result['_model'] ?? $aiAssessment->model),
                    'error_message' => null,
                ]);

                $answer->update([
                    'ai_score' => $score,
                ]);
            });

            return $aiAssessment->fresh(['items']);
        } catch (\Throwable $e) {
            $aiAssessment->update([
                'status' => JobProcessStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function findOrCreateAiAssessment(Answer $answer): AiAssessment
    {
        $existing = AiAssessment::query()
            ->where('answer_id', $answer->id)
            ->whereIn('status', [
                JobProcessStatus::Pending->value,
                JobProcessStatus::Processing->value,
                JobProcessStatus::Failed->value,
            ])
            ->latest('id')
            ->first();

        if ($existing) {
            if ($existing->status === JobProcessStatus::Failed) {
                $existing->update(['attempt' => ((int) $existing->attempt) + 1]);
            }

            return $existing->fresh();
        }

        return AiAssessment::query()->create([
            'submission_id' => $answer->submission_id,
            'answer_id' => $answer->id,
            'provider' => (string) config('ai.provider', 'null'),
            'model' => (string) (config('ai.model') ?: 'unknown'),
            'status' => JobProcessStatus::Pending,
            'attempt' => 1,
        ]);
    }
}
