<?php

namespace App\Jobs;

use App\Enums\JobProcessStatus;
use App\Models\SubmissionFile;
use App\Services\Document\Extractors\ImageOcrExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Dispatch-ready OCR stub. Calls ImageOcrExtractor architecture;
 * fails clearly until a real OCR engine is configured.
 */
class ProcessOCRJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public int $submissionFileId,
    ) {}

    public function handle(ImageOcrExtractor $ocr): void
    {
        $file = SubmissionFile::query()->findOrFail($this->submissionFileId);

        $file->update(['extraction_status' => JobProcessStatus::Processing]);

        $absolute = Storage::disk($file->disk)->path($file->path);

        // Will throw until OCR is configured — intentional architecture hook.
        $text = $ocr->extract($absolute);

        $file->update([
            'extracted_text' => $text,
            'extraction_status' => JobProcessStatus::Completed,
            'ocr_confidence' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        SubmissionFile::query()->whereKey($this->submissionFileId)->update([
            'extraction_status' => JobProcessStatus::Failed,
        ]);
    }
}
