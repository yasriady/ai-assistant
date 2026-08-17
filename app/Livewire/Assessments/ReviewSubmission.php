<?php

namespace App\Livewire\Assessments;

use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\Feedback;
use App\Models\FinalAssessment;
use App\Models\Submission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Review Submission')]
class ReviewSubmission extends Component
{
    use AuthorizesRequests;

    public Assessment $assessment;

    public Submission $submission;

    public float|string $final_score = 0;

    public string $feedback = '';

    public string $lecturer_notes = '';

    public function mount(Assessment $assessment, Submission $submission): void
    {
        abort_unless($submission->assessment_id === $assessment->id, 404);

        $this->authorize('update', $submission);
        $this->assessment = $assessment;
        $this->submission = $submission->load([
            'student',
            'files',
            'aiAssessments.items',
            'finalAssessment',
            'feedback',
        ]);

        $latestAi = $this->submission->aiAssessments->sortByDesc('id')->first();
        $this->final_score = (float) ($this->submission->final_score
            ?? $this->submission->finalAssessment?->score
            ?? $this->submission->ai_score
            ?? $latestAi?->score
            ?? 0);
        $this->feedback = (string) ($this->submission->finalAssessment?->feedback
            ?? $latestAi?->overall_feedback
            ?? '');
        $this->lecturer_notes = (string) ($this->submission->finalAssessment?->lecturer_notes ?? '');
    }

    protected function rules(): array
    {
        return [
            'final_score' => ['required', 'numeric', 'min:0'],
            'feedback' => ['nullable', 'string'],
            'lecturer_notes' => ['nullable', 'string'],
        ];
    }

    public function markReviewed(): void
    {
        $this->validate();
        $this->authorize('update', $this->submission);

        $this->submission->update([
            'final_score' => $this->final_score,
            'status' => SubmissionStatus::Reviewed,
            'reviewed_at' => now(),
        ]);

        if ($this->feedback !== '') {
            Feedback::query()->create([
                'submission_id' => $this->submission->id,
                'user_id' => Auth::id(),
                'body' => $this->feedback,
                'is_ai' => false,
            ]);
        }

        session()->flash('success', __('ui.flash.submission_reviewed'));
        $this->submission->refresh();
    }

    public function finalize(): void
    {
        $this->validate();
        $this->authorize('update', $this->submission);

        DB::transaction(function (): void {
            $latestAi = $this->submission->aiAssessments()->latest('id')->first();

            FinalAssessment::query()->updateOrCreate(
                ['submission_id' => $this->submission->id],
                [
                    'ai_assessment_id' => $latestAi?->id,
                    'score' => $this->final_score,
                    'max_score' => $this->assessment->max_score,
                    'feedback' => $this->feedback,
                    'lecturer_notes' => $this->lecturer_notes,
                    'finalized_by' => Auth::id(),
                    'finalized_at' => now(),
                ],
            );

            $this->submission->update([
                'final_score' => $this->final_score,
                'status' => SubmissionStatus::Finalized,
                'reviewed_at' => $this->submission->reviewed_at ?? now(),
                'finalized_at' => now(),
            ]);
        });

        session()->flash('success', __('ui.flash.submission_finalized'));
        $this->redirect(route('assessments.show', $this->assessment), navigate: true);
    }

    public function render()
    {
        $latestAi = $this->submission->aiAssessments->sortByDesc('id')->first();

        return view('livewire.assessments.review-submission', [
            'latestAi' => $latestAi,
        ])->layoutData(['header' => __('ui.review.header')]);
    }
}
