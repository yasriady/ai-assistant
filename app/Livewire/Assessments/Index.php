<?php

namespace App\Livewire\Assessments;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Models\Assessment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Assessments')]
class Index extends Component
{
    use AuthorizesRequests;
    use ConfirmsDeletion;
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $assessmentId): void
    {
        $assessment = Assessment::query()->findOrFail($assessmentId);
        $this->authorize('delete', $assessment);
        $assessment->delete();
        session()->flash('success', __('ui.flash.assessment_deleted'));
    }

    public function render()
    {
        $user = Auth::user();

        $assessments = Assessment::query()
            ->with(['course', 'rubric'])
            ->withCount('submissions')
            ->whereHas('course', fn ($q) => $q->forActiveTerm())
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where('title', 'like', $term);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.assessments.index', compact('assessments'))
            ->layoutData(['header' => __('ui.assessments.title')]);
    }
}
