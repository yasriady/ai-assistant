<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Services\Questions\QuestionScopeFilter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Exam Builder')]
class ExamBuilder extends Component
{
    use AuthorizesRequests;

    public Assessment $assessment;

    public string $search = '';

    /** @var list<int> */
    public array $selected = [];

    public function mount(Assessment $assessment): void
    {
        $this->authorize('update', $assessment);
        $this->assessment = $assessment->load(['examQuestions.question', 'course']);
        $this->selected = $assessment->examQuestions->pluck('question_id')->map(fn ($id) => (int) $id)->all();
    }

    public function attach(int $questionId): void
    {
        $this->authorize('update', $this->assessment);

        if ($this->assessment->examQuestions()->where('question_id', $questionId)->exists()) {
            return;
        }

        $question = Question::query()->findOrFail($questionId);
        $order = (int) $this->assessment->examQuestions()->max('order_index') + 1;

        ExamQuestion::query()->create([
            'assessment_id' => $this->assessment->id,
            'question_id' => $question->id,
            'order_index' => $order,
            'max_score' => $question->max_score,
        ]);

        $this->assessment->load('examQuestions.question');
        $this->selected = $this->assessment->examQuestions->pluck('question_id')->map(fn ($id) => (int) $id)->all();
        session()->flash('success', __('ui.flash.question_attached'));
    }

    public function detach(int $examQuestionId): void
    {
        $this->authorize('update', $this->assessment);

        ExamQuestion::query()
            ->where('assessment_id', $this->assessment->id)
            ->whereKey($examQuestionId)
            ->delete();

        $this->assessment->load('examQuestions.question');
        $this->selected = $this->assessment->examQuestions->pluck('question_id')->map(fn ($id) => (int) $id)->all();
    }

    public function move(int $examQuestionId, string $direction): void
    {
        $this->authorize('update', $this->assessment);

        $current = ExamQuestion::query()
            ->where('assessment_id', $this->assessment->id)
            ->whereKey($examQuestionId)
            ->firstOrFail();

        $swap = ExamQuestion::query()
            ->where('assessment_id', $this->assessment->id)
            ->when(
                $direction === 'up',
                fn ($q) => $q->where('order_index', '<', $current->order_index)->orderByDesc('order_index'),
                fn ($q) => $q->where('order_index', '>', $current->order_index)->orderBy('order_index'),
            )
            ->first();

        if (! $swap) {
            return;
        }

        $temp = $current->order_index;
        $current->update(['order_index' => $swap->order_index]);
        $swap->update(['order_index' => $temp]);

        $this->assessment->load('examQuestions.question');
    }

    public function render()
    {
        $user = Auth::user();

        $available = Question::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->tap(fn ($q) => app(QuestionScopeFilter::class)->apply($q, $this->assessment))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('question_text', 'like', $term)
                        ->orWhere('topic', 'like', $term);
                });
            })
            ->with('courseTopics')
            ->whereNotIn('id', $this->selected)
            ->latest()
            ->limit(30)
            ->get();

        return view('livewire.assessments.exam-builder', [
            'available' => $available,
        ])->layoutData(['header' => __('ui.exam_builder.title')]);
    }
}
