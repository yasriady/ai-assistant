<?php

namespace App\Livewire\Courses;

use App\Models\CplOutcome;
use App\Models\Course;
use App\Services\Document\DocumentExtractorManager;
use App\Services\Rps\RpsGenerationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Generate RPS')]
class GenerateRps extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Course $course;

    public int $step = 1;

    /** @var list<int> */
    public array $cpl_outcome_ids = [];

    public int $total_weeks = 16;

    public int $midterm_week = 8;

    public string $teaching_notes = '';

    public $reference;

    /** @var array<string, mixed>|null */
    public ?array $draft = null;

    public function mount(Course $course): void
    {
        $this->authorize('update', $course);
        $this->course = $course;
        $this->midterm_week = (int) ($course->midterm_week ?: 8);

        $existing = $course->cplOutcomes()->pluck('cpl_outcomes.id')->map(fn ($id) => (int) $id)->all();
        if ($existing !== []) {
            $this->cpl_outcome_ids = $existing;
        }
    }

    protected function rules(): array
    {
        return [
            'cpl_outcome_ids' => ['required', 'array', 'min:1'],
            'cpl_outcome_ids.*' => ['integer', 'exists:cpl_outcomes,id'],
            'total_weeks' => ['required', 'integer', 'min:4', 'max:20'],
            'midterm_week' => ['required', 'integer', 'min:2', 'max:19'],
            'teaching_notes' => ['nullable', 'string', 'max:5000'],
            'reference' => ['nullable', 'file', 'mimes:pdf,docx,txt', 'max:10240'],
        ];
    }

    public function generate(RpsGenerationService $generator, DocumentExtractorManager $extractors): void
    {
        $this->validate();

        if ($this->midterm_week >= $this->total_weeks) {
            $this->addError('midterm_week', __('ui.rps.generate.midterm_invalid'));

            return;
        }

        $referenceExcerpt = null;

        if ($this->reference) {
            try {
                $path = $this->reference->getRealPath();
                $mime = $this->reference->getMimeType();
                $extension = $this->reference->getClientOriginalExtension();
                $extractor = $extractors->resolve($mime, $extension);
                $text = trim($extractor->extract($path));
                $referenceExcerpt = mb_substr($text, 0, 6000);
            } catch (\Throwable $e) {
                $this->addError('reference', $e->getMessage());

                return;
            }
        }

        try {
            $this->draft = $generator->generate($this->course, $this->cpl_outcome_ids, [
                'total_weeks' => $this->total_weeks,
                'midterm_week' => $this->midterm_week,
                'teaching_notes' => $this->teaching_notes ?: null,
                'reference_excerpt' => $referenceExcerpt,
                'user_id' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            $this->addError('cpl_outcome_ids', $e->getMessage());

            return;
        }

        $this->step = 2;
    }

    public function approve(RpsGenerationService $generator): void
    {
        $this->authorize('update', $this->course);

        if ($this->draft === null) {
            return;
        }

        $generator->apply($this->course, $this->draft, $this->cpl_outcome_ids);

        session()->flash('success', __('ui.flash.rps_generated'));
        $this->redirect(route('courses.rps', $this->course), navigate: true);
    }

    public function back(): void
    {
        $this->step = 1;
    }

    public function render()
    {
        $cplOutcomes = CplOutcome::query()->orderBy('order_index')->get();

        return view('livewire.courses.generate-rps', compact('cplOutcomes'))
            ->layoutData(['header' => __('ui.rps.generate.title')]);
    }
}
