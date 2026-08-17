<?php

namespace App\Jobs;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Services\Assessment\DocumentAssessmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExtractDocumentTextJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public int $submissionId,
        public bool $chainAssess = true,
    ) {}

    public function handle(DocumentAssessmentService $documents): void
    {
        $submission = Submission::query()->with('files')->findOrFail($this->submissionId);

        $submission->update(['status' => SubmissionStatus::Processing]);

        $submission = $documents->extract($submission);

        if ($this->chainAssess) {
            AssessDocumentJob::dispatch($submission->id);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Submission::query()->whereKey($this->submissionId)->update([
            'status' => SubmissionStatus::Failed,
            'failure_reason' => $exception?->getMessage() ?? 'Document extraction failed.',
        ]);
    }
}
