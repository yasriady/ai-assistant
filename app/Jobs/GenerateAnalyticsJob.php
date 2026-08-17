<?php

namespace App\Jobs;

use App\Models\Assessment;
use App\Services\Analytics\ExamAnalyticsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAnalyticsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public int $assessmentId,
        public ?float $passMark = null,
    ) {}

    public function handle(ExamAnalyticsService $analytics): void
    {
        $assessment = Assessment::query()->findOrFail($this->assessmentId);
        $analytics->generateAndStore($assessment, $this->passMark);
    }
}
