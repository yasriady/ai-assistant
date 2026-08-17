<?php

namespace App\Livewire\Courses;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Courses')]
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

    public function delete(int $courseId): void
    {
        $course = Course::query()->findOrFail($courseId);
        $this->authorize('delete', $course);
        $course->delete();

        session()->flash('success', __('ui.flash.course_deleted'));
    }

    public function render()
    {
        $user = Auth::user();

        $courses = Course::query()
            ->forActiveTerm()
            ->withCount(['students', 'assessments'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('class_name', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.courses.index', compact('courses'))
            ->layoutData(['header' => __('ui.courses.title')]);
    }
}
