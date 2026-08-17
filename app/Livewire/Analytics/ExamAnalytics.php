<?php

namespace App\Livewire\Analytics;

use App\Models\Assessment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Exam Analytics')]
class ExamAnalytics extends Component
{
    use AuthorizesRequests;

    public Assessment $assessment;

    public function mount(Assessment $assessment): void
    {
        $this->authorize('view', $assessment);
        $this->assessment = $assessment->load(['course', 'examQuestions.question']);
    }

    public function render()
    {
        $submissions = $this->assessment->submissions()->with('student')->get();

        $scores = $submissions
            ->map(fn ($submission) => $submission->final_score ?? $submission->ai_score)
            ->filter(fn ($score) => $score !== null)
            ->map(fn ($score) => (float) $score)
            ->values();

        $summary = [
            'total' => $submissions->count(),
            'finalized' => $submissions->where('status.value', 'finalized')->count()
                ?: $submissions->filter(fn ($s) => $s->status?->value === 'finalized')->count(),
            'average' => $scores->avg(),
            'median' => $this->median($scores->all()),
            'min' => $scores->min(),
            'max' => $scores->max(),
        ];

        $statusBreakdown = $submissions
            ->groupBy(fn ($s) => $s->status?->value ?? 'unknown')
            ->map->count();

        $questionStats = collect();

        if ($this->assessment->examQuestions->isNotEmpty()) {
            $questionStats = DB::table('answers')
                ->join('exam_questions', 'answers.exam_question_id', '=', 'exam_questions.id')
                ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
                ->where('exam_questions.assessment_id', $this->assessment->id)
                ->selectRaw('questions.id as question_id, questions.question_text, AVG(COALESCE(answers.final_score, answers.ai_score)) as avg_score, COUNT(answers.id) as answer_count')                ->groupBy('questions.id', 'questions.question_text')
                ->orderBy('questions.id')
                ->get();
        }

        return view('livewire.analytics.exam-analytics', compact('summary', 'statusBreakdown', 'questionStats', 'submissions'))
            ->layoutData(['header' => __('ui.analytics.title')]);
    }

    /**
     * @param  list<float>  $values
     */
    private function median(array $values): ?float
    {
        if ($values === []) {
            return null;
        }

        sort($values);
        $count = count($values);
        $middle = intdiv($count, 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }
}
