<?php

namespace App\Services\Rps;

use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\CplOutcome;
use App\Models\Cpmk;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\DB;

class RpsGenerationService
{
    public function __construct(
        protected AIManager $ai,
        protected RpsDraftValidator $validator,
    ) {}

    /**
     * @param  list<int>  $cplOutcomeIds
     * @param  array<string, mixed>  $options
     * @return array{midterm_week: int, cpmks: list<array<string, mixed>>, topics: list<array<string, mixed>>}
     */
    public function generate(Course $course, array $cplOutcomeIds, array $options = []): array
    {
        $cpls = CplOutcome::query()
            ->whereIn('id', $cplOutcomeIds)
            ->orderBy('order_index')
            ->get()
            ->map(fn (CplOutcome $cpl): array => [
                'code' => $cpl->code,
                'official_code' => $cpl->official_code,
                'category' => $cpl->category->value,
                'description' => $cpl->description,
            ])
            ->values()
            ->all();

        if ($cpls === []) {
            throw new \InvalidArgumentException(__('ui.rps.generate.cpl_required'));
        }

        $payload = [
            'course' => [
                'code' => $course->code,
                'name' => $course->name,
                'description' => $course->description,
                'term_code' => $course->term_code,
            ],
            'cpls' => $cpls,
            'total_weeks' => (int) ($options['total_weeks'] ?? 16),
            'midterm_week' => (int) ($options['midterm_week'] ?? 8),
            'teaching_notes' => $options['teaching_notes'] ?? null,
            'reference_excerpt' => $options['reference_excerpt'] ?? null,
        ];

        $raw = $this->ai->generateRpsDraft($payload, [
            'user_id' => $options['user_id'] ?? null,
            'method' => 'generateRpsDraft',
        ]);

        return $this->validator->validate($raw);
    }

    /**
     * @param  array{midterm_week: int, cpmks: list<array{code: string, description: string, cpl_codes: list<string>}>, topics: list<array{week_number: int, title: string, description: string, cpmk_codes: list<string>}>}  $draft
     * @param  list<int>  $cplOutcomeIds
     */
    public function apply(Course $course, array $draft, array $cplOutcomeIds): void
    {
        DB::transaction(function () use ($course, $draft, $cplOutcomeIds): void {
            $course->update(['midterm_week' => $draft['midterm_week']]);
            $course->cplOutcomes()->sync($cplOutcomeIds);

            $cpmkMap = [];

            foreach ($draft['cpmks'] as $index => $row) {
                $cpmk = Cpmk::query()->updateOrCreate(
                    ['course_id' => $course->id, 'code' => $row['code']],
                    [
                        'description' => $row['description'],
                        'order_index' => $index,
                    ]
                );
                $cpmkMap[$row['code']] = $cpmk->id;
            }

            Cpmk::query()
                ->where('course_id', $course->id)
                ->whereNotIn('id', array_values($cpmkMap))
                ->delete();

            $keptTopicIds = [];

            foreach ($draft['topics'] as $index => $row) {
                $topic = CourseTopic::query()->updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'week_number' => $row['week_number'],
                    ],
                    [
                        'title' => $row['title'],
                        'description' => $row['description'] ?: null,
                        'order_index' => $index,
                    ]
                );

                $cpmkIds = collect($row['cpmk_codes'])
                    ->map(fn (string $code) => $cpmkMap[$code] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                $topic->cpmks()->sync($cpmkIds);
                $keptTopicIds[] = $topic->id;
            }

            CourseTopic::query()
                ->where('course_id', $course->id)
                ->whereNotIn('id', $keptTopicIds)
                ->delete();
        });
    }
}
