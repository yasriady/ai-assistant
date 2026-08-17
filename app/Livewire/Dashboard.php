<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Student;
use App\Models\Submission;
use App\Services\Term\ActiveTerm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $term = app(ActiveTerm::class)->current();

        $courseQuery = Course::query()->forTerm($term);
        $assessmentQuery = Assessment::query()->whereHas('course', fn ($q) => $q->forTerm($term));
        $submissionQuery = Submission::query()->whereHas('assessment', function ($query) use ($user, $term): void {
            $query->whereHas('course', fn ($q) => $q->forTerm($term));
            if (! $user->isAdmin()) {
                $query->where('user_id', $user->id);
            }
        });

        if (! $user->isAdmin()) {
            $courseQuery->where('user_id', $user->id);
            $assessmentQuery->where('user_id', $user->id);
        }

        $stats = [
            'courses' => $courseQuery->count(),
            'assessments' => $assessmentQuery->count(),
            'students' => Student::query()
                ->whereHas('courses', function ($query) use ($user, $term): void {
                    $query->forTerm($term);
                    if (! $user->isAdmin()) {
                        $query->where('user_id', $user->id);
                    }
                })
                ->count(),
            'pending_reviews' => (clone $submissionQuery)
                ->whereIn('status', ['assessed', 'reviewed'])
                ->count(),
        ];

        $recentAssessments = Assessment::query()
            ->with('course')
            ->whereHas('course', fn ($q) => $q->forTerm($term))
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->limit(6)
            ->get();

        $recentSubmissions = Submission::query()
            ->with(['student', 'assessment'])
            ->whereHas('assessment', function ($query) use ($user, $term): void {
                $query->whereHas('course', fn ($q) => $q->forTerm($term));
                if (! $user->isAdmin()) {
                    $query->where('user_id', $user->id);
                }
            })
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.dashboard', compact('stats', 'recentAssessments', 'recentSubmissions'))
            ->layoutData(['header' => __('ui.dashboard.header')]);
    }
}
