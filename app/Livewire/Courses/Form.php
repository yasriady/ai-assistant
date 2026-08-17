<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Services\Term\ActiveTerm;
use App\Support\AcademicTerm;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Course Form')]
class Form extends Component
{
    use AuthorizesRequests;

    public ?Course $course = null;

    public string $code = '';

    public string $name = '';

    public string $term_code = '';

    public string $class_name = '';

    public string $description = '';

    public bool $is_active = true;

    public function mount(?Course $course = null): void
    {
        if ($course?->exists) {
            $this->authorize('update', $course);
            $this->course = $course;
            $this->code = (string) $course->code;
            $this->name = (string) $course->name;
            $this->term_code = (string) ($course->term_code ?: app(ActiveTerm::class)->current());
            $this->class_name = (string) ($course->class_name ?? '');
            $this->description = (string) ($course->description ?? '');
            $this->is_active = (bool) $course->is_active;
        } else {
            $this->authorize('create', Course::class);
            $this->term_code = app(ActiveTerm::class)->current();
        }
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'term_code' => ['required', 'string', 'size:5', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || ! AcademicTerm::isValid($value)) {
                    $fail(__('ui.term.invalid'));
                }
            }],
            'class_name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $termCode = AcademicTerm::assertValid($data['term_code']);

        $payload = [
            ...$data,
            'term_code' => $termCode,
            'semester' => AcademicTerm::semesterName($termCode, 'id'),
            'academic_year' => AcademicTerm::academicYear($termCode),
        ];

        if ($this->course) {
            $this->authorize('update', $this->course);
            $this->course->update($payload);
            session()->flash('success', __('ui.flash.course_updated'));
        } else {
            $this->authorize('create', Course::class);
            Course::query()->create([
                ...$payload,
                'user_id' => Auth::id(),
            ]);
            session()->flash('success', __('ui.flash.course_created'));
        }

        $this->redirect(route('courses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.courses.form', [
            'termChoices' => AcademicTerm::options($this->term_code ?: app(ActiveTerm::class)->current(), before: 4, after: 1),
        ])->layoutData(['header' => $this->course ? __('ui.courses.edit_title') : __('ui.courses.create_title')]);
    }
}
