<?php

namespace App\Services\Assessment;

use App\Enums\JobProcessStatus;
use App\Enums\SubmissionStatus;
use App\Models\AiAssessment;
use App\Models\AiAssessmentItem;
use App\Models\Rubric;
use App\Models\Submission;
use App\Services\AI\AIManager;
use App\Services\Document\DocumentExtractorManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DocumentAssessmentService
{
    public function __construct(
        protected DocumentExtractorManager $extractors,
        protected AIManager $ai,
        protected RubricScoringService $rubricScoring,
        protected SubmissionStudentResolver $studentResolver,
    ) {}

    /**
     * Extract text for all submission files and persist on submission.
     */
    public function extract(Submission $submission): Submission
    {
        $submission->loadMissing('files');
        $combined = [];

        foreach ($submission->files as $file) {
            $file->update(['extraction_status' => JobProcessStatus::Processing]);

            try {
                $absolute = Storage::disk($file->disk)->path($file->path);
                $text = $this->extractors->extract(
                    $absolute,
                    $file->mime_type,
                    pathinfo($file->original_name, PATHINFO_EXTENSION),
                );

                $file->update([
                    'extracted_text' => $text,
                    'extraction_status' => JobProcessStatus::Completed,
                ]);

                if ($text !== '') {
                    $combined[] = $text;
                }
            } catch (\Throwable $e) {
                $file->update([
                    'extraction_status' => JobProcessStatus::Failed,
                ]);

                throw $e;
            }
        }

        $submission->update([
            'extracted_text' => implode("\n\n", $combined),
            'status' => SubmissionStatus::Processing,
        ]);

        return $this->studentResolver->resolve(
            $submission->fresh(['files', 'student', 'assessment.course'])
        );
    }

    /**
     * Run AI assessment for a document submission (idempotent AiAssessment record).
     */
    public function assess(Submission $submission, bool $extractIfMissing = true): AiAssessment
    {
        $submission->loadMissing(['assessment.rubric.criteria.levels', 'files']);

        if ($extractIfMissing && blank($submission->extracted_text)) {
            $this->extract($submission);
            $submission->refresh();
        }

        if (blank($submission->extracted_text)) {
            throw new RuntimeException('Submission has no extracted text to assess.');
        }

        $assessment = $submission->assessment;
        $rubric = $assessment?->rubric;
        $maxScore = (float) ($assessment?->max_score ?? 100);

        $aiAssessment = $this->findOrCreateAiAssessment($submission);

        $aiAssessment->update([
            'status' => JobProcessStatus::Processing,
            'error_message' => null,
            'provider' => (string) config('ai.provider', 'null'),
            'model' => (string) (config('ai.model') ?: config('ai.providers.'.config('ai.provider').'.model') ?: 'unknown'),
            'prompt_version' => (string) config('ai.prompt_version', '1.0'),
            'rubric_version' => (string) ($assessment?->rubric_version ?? $rubric?->version ?? ''),
            'assessment_version' => (string) ($assessment?->id ?? ''),
        ]);

        try {
            $payload = [
                'document_text' => $submission->extracted_text,
                'max_score' => $maxScore,
                'instructions' => $assessment?->instructions,
                'rubric' => $this->rubricPayload($rubric),
            ];

            $result = $this->ai->assessDocument($payload, [
                'user_id' => $assessment?->user_id,
                'assessment_id' => $assessment?->id,
                'submission_id' => $submission->id,
                'method' => 'assessDocument',
            ]);

            $scored = $this->rubricScoring->calculate(
                $result['criteria'] ?? [],
                $maxScore,
            );

            // Prefer model score when present; fall back to weighted rubric calc.
            $finalScore = isset($result['score']) ? (float) $result['score'] : $scored['score'];

            DB::transaction(function () use ($aiAssessment, $result, $finalScore, $maxScore, $submission): void {
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
                    'score' => $finalScore,
                    'max_score' => $maxScore,
                    'confidence' => $result['confidence'] ?? null,
                    'overall_feedback' => $result['overall_feedback'] ?? null,
                    'raw_response' => $result['_raw'] ?? $result,
                    'structured_result' => [
                        'score' => $finalScore,
                        'max_score' => $maxScore,
                        'criteria' => $result['criteria'] ?? [],
                        'overall_feedback' => $result['overall_feedback'] ?? null,
                        'confidence' => $result['confidence'] ?? null,
                    ],
                    'provider' => (string) ($result['_provider'] ?? $aiAssessment->provider),
                    'model' => (string) ($result['_model'] ?? $aiAssessment->model),
                    'error_message' => null,
                ]);

                $submission->update([
                    'ai_score' => $finalScore,
                    'status' => SubmissionStatus::Assessed,
                    'processed_at' => now(),
                    'failure_reason' => null,
                ]);
            });

            return $aiAssessment->fresh(['items']);
        } catch (\Throwable $e) {
            $aiAssessment->update([
                'status' => JobProcessStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            $submission->update([
                'status' => SubmissionStatus::Failed,
                'failure_reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function findOrCreateAiAssessment(Submission $submission): AiAssessment
    {
        $existing = AiAssessment::query()
            ->where('submission_id', $submission->id)
            ->whereNull('answer_id')
            ->whereIn('status', [
                JobProcessStatus::Pending->value,
                JobProcessStatus::Processing->value,
                JobProcessStatus::Failed->value,
            ])
            ->latest('id')
            ->first();

        if ($existing) {
            $existing->update([
                'attempt' => ((int) $existing->attempt) + ($existing->status === JobProcessStatus::Failed ? 1 : 0),
            ]);

            return $existing->fresh();
        }

        // Reuse completed record only when forcing re-assess via failed/pending path above.
        // New pending record for first run:
        return AiAssessment::query()->create([
            'submission_id' => $submission->id,
            'answer_id' => null,
            'provider' => (string) config('ai.provider', 'null'),
            'model' => (string) (config('ai.model') ?: 'unknown'),
            'status' => JobProcessStatus::Pending,
            'attempt' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rubricPayload(?Rubric $rubric): array
    {
        if (! $rubric) {
            return ['criteria' => []];
        }

        $rubric->loadMissing('criteria.levels');

        return [
            'id' => $rubric->id,
            'name' => $rubric->name,
            'version' => $rubric->version,
            'description' => $rubric->description,
            'criteria' => $rubric->criteria->map(fn ($c) => [
                'name' => $c->name,
                'description' => $c->description,
                'weight' => (float) $c->weight,
                'max_score' => (float) $c->max_score,
                'levels' => $c->levels->map(fn ($l) => [
                    'name' => $l->name,
                    'description' => $l->description,
                    'min_score' => (float) $l->min_score,
                    'max_score' => (float) $l->max_score,
                ])->all(),
            ])->all(),
        ];
    }
}
