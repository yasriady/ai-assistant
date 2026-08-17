<?php

namespace App\Livewire\Students;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Student Form')]
class Form extends Component
{
    public ?Student $student = null;

    public string $nim = '';

    public string $name = '';

    public string $email = '';

    public string $program = '';

    public string $class_name = '';

    /** @var list<int> */
    public array $course_ids = [];

    public function mount(?Student $student = null): void
    {
        if ($student?->exists) {
            $this->student = $student;
            $this->nim = (string) $student->nim;
            $this->name = (string) $student->name;
            $this->email = (string) ($student->email ?? '');
            $this->program = (string) ($student->program ?? '');
            $this->class_name = (string) ($student->class_name ?? '');
            $this->course_ids = $student->courses()->pluck('courses.id')->map(fn ($id) => (int) $id)->all();
        }
    }

    protected function rules(): array
    {
        return [
            'nim' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'nim')->ignore($this->student?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'course_ids' => ['array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $courseIds = $data['course_ids'] ?? [];
        unset($data['course_ids']);

        if ($this->student) {
            $this->student->update($data);
            $this->student->courses()->sync($courseIds);
            session()->flash('success', __('ui.flash.student_updated'));
        } else {
            $student = Student::query()->create($data);
            $student->courses()->sync($courseIds);
            session()->flash('success', __('ui.flash.student_created'));
        }

        $this->redirect(route('students.index'), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();

        $courses = Course::query()
            ->forActiveTerm()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        return view('livewire.students.form', compact('courses'))
            ->layoutData(['header' => $this->student ? __('ui.students.edit_title') : __('ui.students.create_title')]);
    }
}
