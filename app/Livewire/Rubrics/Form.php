<?php

namespace App\Livewire\Rubrics;

use App\Models\Course;
use App\Models\Rubric;
use App\Models\RubricCriterion;
use App\Models\RubricLevel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Rubric Form')]
class Form extends Component
{
    use AuthorizesRequests;

    public ?Rubric $rubric = null;

    public string $name = '';

    public string $description = '';

    public ?int $course_id = null;

    public bool $is_template = false;

    /** @var list<array<string, mixed>> */
    public array $criteria = [];

    public function mount(?Rubric $rubric = null): void
    {
        if ($rubric?->exists) {
            $this->authorize('update', $rubric);
            $this->rubric = $rubric->load('criteria.levels');
            $this->fill($rubric->only(['name', 'description', 'course_id', 'is_template']));

            $this->criteria = $rubric->criteria->map(function (RubricCriterion $criterion): array {
                return [
                    'name' => $criterion->name,
                    'description' => $criterion->description ?? '',
                    'weight' => (float) $criterion->weight,
                    'max_score' => (float) $criterion->max_score,
                    'levels' => $criterion->levels->map(fn (RubricLevel $level): array => [
                        'name' => $level->name,
                        'description' => $level->description ?? '',
                        'min_score' => (float) $level->min_score,
                        'max_score' => (float) $level->max_score,
                    ])->values()->all(),
                ];
            })->values()->all();
        } else {
            $this->authorize('create', Rubric::class);
            $this->addCriterion();
        }
    }

    public function addCriterion(): void
    {
        $this->criteria[] = [
            'name' => '',
            'description' => '',
            'weight' => 0,
            'max_score' => 100,
            'levels' => [
                ['name' => 'Excellent', 'description' => '', 'min_score' => 80, 'max_score' => 100],
                ['name' => 'Good', 'description' => '', 'min_score' => 60, 'max_score' => 79],
                ['name' => 'Fair', 'description' => '', 'min_score' => 40, 'max_score' => 59],
                ['name' => 'Poor', 'description' => '', 'min_score' => 0, 'max_score' => 39],
            ],
        ];
    }

    public function removeCriterion(int $index): void
    {
        unset($this->criteria[$index]);
        $this->criteria = array_values($this->criteria);
    }

    public function addLevel(int $criterionIndex): void
    {
        $this->criteria[$criterionIndex]['levels'][] = [
            'name' => '',
            'description' => '',
            'min_score' => 0,
            'max_score' => 0,
        ];
    }

    public function removeLevel(int $criterionIndex, int $levelIndex): void
    {
        unset($this->criteria[$criterionIndex]['levels'][$levelIndex]);
        $this->criteria[$criterionIndex]['levels'] = array_values($this->criteria[$criterionIndex]['levels']);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'is_template' => ['boolean'],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.name' => ['required', 'string', 'max:255'],
            'criteria.*.description' => ['nullable', 'string'],
            'criteria.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'criteria.*.max_score' => ['required', 'numeric', 'min:0'],
            'criteria.*.levels' => ['required', 'array', 'min:1'],
            'criteria.*.levels.*.name' => ['required', 'string', 'max:255'],
            'criteria.*.levels.*.description' => ['nullable', 'string'],
            'criteria.*.levels.*.min_score' => ['required', 'numeric'],
            'criteria.*.levels.*.max_score' => ['required', 'numeric'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data): void {
            if ($this->rubric) {
                $this->authorize('update', $this->rubric);
                $this->rubric->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'course_id' => $data['course_id'],
                    'is_template' => $data['is_template'],
                    'version' => $this->rubric->version + 1,
                ]);
                $this->rubric->criteria()->each(function (RubricCriterion $criterion): void {
                    $criterion->levels()->delete();
                    $criterion->delete();
                });
                $rubric = $this->rubric->fresh();
            } else {
                $this->authorize('create', Rubric::class);
                $rubric = Rubric::query()->create([
                    'user_id' => Auth::id(),
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'course_id' => $data['course_id'],
                    'is_template' => $data['is_template'],
                    'version' => 1,
                ]);
            }

            foreach ($data['criteria'] as $criterionIndex => $criterionData) {
                $criterion = $rubric->criteria()->create([
                    'name' => $criterionData['name'],
                    'description' => $criterionData['description'] ?? null,
                    'weight' => $criterionData['weight'],
                    'max_score' => $criterionData['max_score'],
                    'order_index' => $criterionIndex,
                ]);

                foreach ($criterionData['levels'] as $levelIndex => $levelData) {
                    $criterion->levels()->create([
                        'name' => $levelData['name'],
                        'description' => $levelData['description'] ?? null,
                        'min_score' => $levelData['min_score'],
                        'max_score' => $levelData['max_score'],
                        'order_index' => $levelIndex,
                    ]);
                }
            }
        });

        session()->flash('success', __('ui.flash.rubric_saved'));
        $this->redirect(route('rubrics.index'), navigate: true);
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

        return view('livewire.rubrics.form', compact('courses'))
            ->layoutData(['header' => $this->rubric ? __('ui.rubrics.edit_title') : __('ui.rubrics.create_title')]);
    }
}
