<?php

namespace App\Livewire\Assessments;

use App\Enums\AssessmentEngine;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Rubric;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Assessment Form')]
class Form extends Component
{
    use AuthorizesRequests;

    public ?Assessment $assessment = null;

    public ?int $course_id = null;

    public ?int $rubric_id = null;

    public string $title = '';

    public string $description = '';

    public string $type = 'assignment';

    public string $instructions = '';

    public ?string $due_at = null;

    public float|string $max_score = 100;

    public string $status = 'draft';

    public function mount(?Assessment $assessment = null): void
    {
        if ($assessment?->exists) {
            $this->authorize('update', $assessment);
            $this->assessment = $assessment;
            $this->course_id = $assessment->course_id;
            $this->rubric_id = $assessment->rubric_id;
            $this->title = $assessment->title;
            $this->description = $assessment->description ?? '';
            $this->type = $assessment->type->value;
            $this->instructions = $assessment->instructions ?? '';
            $this->due_at = optional($assessment->due_at)?->format('Y-m-d\TH:i');
            $this->max_score = (float) $assessment->max_score;
            $this->status = $assessment->status;
        } else {
            $this->authorize('create', Assessment::class);
        }
    }

    protected function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'rubric_id' => ['nullable', 'integer', 'exists:rubrics,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(AssessmentType::class)],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'max_score' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'closed'])],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $type = AssessmentType::from($data['type']);

        $payload = [
            ...$data,
            'type' => $type,
            'engine' => $type->engine(),
            'user_id' => Auth::id(),
            'due_at' => $data['due_at'] ?: null,
        ];

        if ($this->assessment) {
            $this->authorize('update', $this->assessment);
            unset($payload['user_id']);
            if ($this->assessment->rubric_id !== ($payload['rubric_id'] ?? null)) {
                $payload['rubric_version'] = Rubric::query()->find($payload['rubric_id'] ?? 0)?->version;
            }
            $this->assessment->update($payload);
            session()->flash('success', __('ui.flash.assessment_updated'));
            $this->redirect(route('assessments.show', $this->assessment), navigate: true);
        } else {
            $this->authorize('create', Assessment::class);
            $payload['rubric_version'] = Rubric::query()->find($payload['rubric_id'] ?? 0)?->version;
            $assessment = Assessment::query()->create($payload);
            session()->flash('success', __('ui.flash.assessment_created'));
            $this->redirect(route('assessments.show', $assessment), navigate: true);
        }
    }

    public function render()
    {
        $user = Auth::user();

        $activeTerm = app(\App\Services\Term\ActiveTerm::class)->current();

        $courses = Course::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->where(function ($query) use ($activeTerm): void {
                $query->where('term_code', $activeTerm);
                if ($this->course_id) {
                    $query->orWhere('id', $this->course_id);
                }
            })
            ->orderBy('name')
            ->get();

        $rubrics = Rubric::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        return view('livewire.assessments.form', [
            'courses' => $courses,
            'rubrics' => $rubrics,
            'types' => AssessmentType::cases(),
            'engines' => AssessmentEngine::cases(),
        ])->layoutData(['header' => $this->assessment ? __('ui.assessments.edit_title') : __('ui.assessments.create_title')]);
    }
}
