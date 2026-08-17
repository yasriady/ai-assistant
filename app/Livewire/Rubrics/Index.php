<?php

namespace App\Livewire\Rubrics;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Models\Rubric;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Rubrics')]
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

    public function delete(int $rubricId): void
    {
        $rubric = Rubric::query()->findOrFail($rubricId);
        $this->authorize('delete', $rubric);
        $rubric->delete();
        session()->flash('success', __('ui.flash.rubric_deleted'));
    }

    public function render()
    {
        $user = Auth::user();

        $rubrics = Rubric::query()
            ->with('course')
            ->withCount('criteria')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where('name', 'like', $term);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.rubrics.index', compact('rubrics'))
            ->layoutData(['header' => __('ui.rubrics.title')]);
    }
}
