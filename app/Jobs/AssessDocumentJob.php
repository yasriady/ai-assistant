<?php

namespace App\Jobs;

use App\Enums\JobProcessStatus;
use App\Enums\SubmissionStatus;
use App\Models\AiAssessment;
use App\Models\Submission;
use App\Services\Assessment\DocumentAssessmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AssessDocumentJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public int $submissionId,
    ) {}

    public function handle(DocumentAssessmentService $documents): void
    {
        $submission = Submission::query()->findOrFail($this->submissionId);

        // Idempotent: if a completed document AI assessment already exists, skip.
        $completed = AiAssessment::query()
            ->where('submission_id', $submission->id)
            ->whereNull('answer_id')
            ->where('status', JobProcessStatus::Completed)
            ->exists();

        if ($completed && $submission->status === SubmissionStatus::Assessed) {
            return;
        }

        $documents->assess($submission, extractIfMissing: true);
    }

    public function failed(?Throwable $exception): void
    {
        $submission = Submission::query()->find($this->submissionId);
        if (! $submission) {
            return;
        }

        $submission->update([
            'status' => SubmissionStatus::Failed,
            'failure_reason' => $exception?->getMessage() ?? 'Document assessment failed.',
        ]);

        AiAssessment::query()
            ->where('submission_id', $this->submissionId)
            ->whereNull('answer_id')
            ->whereIn('status', [
                JobProcessStatus::Pending->value,
                JobProcessStatus::Processing->value,
            ])
            ->update([
                'status' => JobProcessStatus::Failed,
                'error_message' => $exception?->getMessage(),
            ]);
    }
}
