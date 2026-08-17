<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Cpmk;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('RPS')]
class Rps extends Component
{
    use AuthorizesRequests;

    public Course $course;

    public int $midterm_week = 8;

    /** @var list<array{id: int|null, code: string, description: string, order_index: int}> */
    public array $cpmks = [];

    /** @var list<array{id: int|null, week_number: int, title: string, description: string, order_index: int, cpmk_ids: list<int>}> */
    public array $topics = [];

    public function mount(Course $course): void
    {
        $this->authorize('update', $course);
        $this->course = $course;
        $this->midterm_week = (int) ($course->midterm_week ?: 8);
        $this->loadData();
    }

    private function loadData(): void
    {
        $this->course->load(['cpmks', 'topics.cpmks']);

        $this->cpmks = $this->course->cpmks->map(fn (Cpmk $cpmk): array => [
            'id' => $cpmk->id,
            'code' => $cpmk->code,
            'description' => $cpmk->description,
            'order_index' => $cpmk->order_index,
        ])->values()->all();

        $this->topics = $this->course->topics->map(fn (CourseTopic $topic): array => [
            'id' => $topic->id,
            'week_number' => $topic->week_number,
            'title' => $topic->title,
            'description' => $topic->description ?? '',
            'order_index' => $topic->order_index,
            'cpmk_ids' => $topic->cpmks->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ])->values()->all();
    }

    public function addCpmk(): void
    {
        $this->cpmks[] = [
            'id' => null,
            'code' => '',
            'description' => '',
            'order_index' => count($this->cpmks),
        ];
    }

    public function removeCpmk(int $index): void
    {
        unset($this->cpmks[$index]);
        $this->cpmks = array_values($this->cpmks);
    }

    public function addTopic(): void
    {
        $this->topics[] = [
            'id' => null,
            'week_number' => count($this->topics) + 1,
            'title' => '',
            'description' => '',
            'order_index' => count($this->topics),
            'cpmk_ids' => [],
        ];
    }

    public function removeTopic(int $index): void
    {
        unset($this->topics[$index]);
        $this->topics = array_values($this->topics);
    }

    protected function rules(): array
    {
        return [
            'midterm_week' => ['required', 'integer', 'min:1', 'max:20'],
            'cpmks' => ['array'],
            'cpmks.*.code' => ['required', 'string', 'max:50'],
            'cpmks.*.description' => ['required', 'string'],
            'cpmks.*.order_index' => ['integer', 'min:0'],
            'topics' => ['array'],
            'topics.*.week_number' => ['required', 'integer', 'min:1', 'max:20'],
            'topics.*.title' => ['required', 'string', 'max:255'],
            'topics.*.description' => ['nullable', 'string'],
            'topics.*.order_index' => ['integer', 'min:0'],
            'topics.*.cpmk_ids' => ['array'],
            'topics.*.cpmk_ids.*' => ['integer'],
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->course);
        $data = $this->validate();

        DB::transaction(function () use ($data): void {
            $this->course->update(['midterm_week' => $data['midterm_week']]);

            $keptCpmkIds = [];

            foreach ($data['cpmks'] as $index => $row) {
                $cpmk = isset($row['id'])
                    ? Cpmk::query()->where('course_id', $this->course->id)->find($row['id'])
                    : null;

                if ($cpmk) {
                    $cpmk->update([
                        'code' => $row['code'],
                        'description' => $row['description'],
                        'order_index' => $row['order_index'] ?? $index,
                    ]);
                } else {
                    $cpmk = Cpmk::query()->create([
                        'course_id' => $this->course->id,
                        'code' => $row['code'],
                        'description' => $row['description'],
                        'order_index' => $row['order_index'] ?? $index,
                    ]);
                }

                $keptCpmkIds[] = $cpmk->id;
            }

            Cpmk::query()
                ->where('course_id', $this->course->id)
                ->whereNotIn('id', $keptCpmkIds)
                ->delete();

            $keptTopicIds = [];

            foreach ($data['topics'] as $index => $row) {
                $topic = isset($row['id'])
                    ? CourseTopic::query()->where('course_id', $this->course->id)->find($row['id'])
                    : null;

                if ($topic) {
                    $topic->update([
                        'week_number' => $row['week_number'],
                        'title' => $row['title'],
                        'description' => $row['description'] ?: null,
                        'order_index' => $row['order_index'] ?? $index,
                    ]);
                } else {
                    $topic = CourseTopic::query()->create([
                        'course_id' => $this->course->id,
                        'week_number' => $row['week_number'],
                        'title' => $row['title'],
                        'description' => $row['description'] ?: null,
                        'order_index' => $row['order_index'] ?? $index,
                    ]);
                }

                $cpmkIds = collect($row['cpmk_ids'] ?? [])
                    ->filter(fn ($id) => in_array((int) $id, $keptCpmkIds, true))
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                $topic->cpmks()->sync($cpmkIds);
                $keptTopicIds[] = $topic->id;
            }

            CourseTopic::query()
                ->where('course_id', $this->course->id)
                ->whereNotIn('id', $keptTopicIds)
                ->delete();
        });

        $this->course->refresh();
        $this->loadData();

        session()->flash('success', __('ui.flash.rps_saved'));
    }

    public function render()
    {
        return view('livewire.courses.rps')
            ->layoutData(['header' => __('ui.rps.title')]);
    }
}
