<?php

namespace App\Jobs;

use App\Enums\JobProcessStatus;
use App\Models\AiAssessment;
use App\Models\Answer;
use App\Services\Assessment\ExamAssessmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AssessAnswerJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public int $answerId,
    ) {}

    public function handle(ExamAssessmentService $exams): void
    {
        $answer = Answer::query()->with(['question.options', 'question.rubric.criteria'])->findOrFail($this->answerId);

        $completed = AiAssessment::query()
            ->where('answer_id', $answer->id)
            ->where('status', JobProcessStatus::Completed)
            ->exists();

        if ($completed && $answer->ai_score !== null) {
            return;
        }

        $exams->assessAnswer($answer);
    }

    public function failed(?Throwable $exception): void
    {
        AiAssessment::query()
            ->where('answer_id', $this->answerId)
            ->whereIn('status', [
                JobProcessStatus::Pending->value,
                JobProcessStatus::Processing->value,
            ])
            ->update([
                'status' => JobProcessStatus::Failed,
                'error_message' => $exception?->getMessage() ?? 'Answer assessment failed.',
            ]);
    }
}
