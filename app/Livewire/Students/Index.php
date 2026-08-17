<?php

namespace App\Livewire\Students;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Students')]
class Index extends Component
{
    use ConfirmsDeletion;
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $studentId): void
    {
        $student = Student::query()->findOrFail($studentId);
        $student->delete();
        session()->flash('success', __('ui.flash.student_deleted'));
    }

    public function render()
    {
        $user = Auth::user();

        $students = Student::query()
            ->with(['courses' => function ($query) use ($user): void {
                $query->forActiveTerm();
                if (! $user->isAdmin()) {
                    $query->where('user_id', $user->id);
                }
            }])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('nim', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->whereHas('courses', function ($query) use ($user): void {
                $query->forActiveTerm();
                if (! $user->isAdmin()) {
                    $query->where('user_id', $user->id);
                }
            })
            ->latest()
            ->paginate(12);

        return view('livewire.students.index', compact('students'))
            ->layoutData(['header' => __('ui.students.title')]);
    }
}
