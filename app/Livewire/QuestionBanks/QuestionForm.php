<?php

namespace App\Livewire\QuestionBanks;

use App\Enums\CognitiveLevel;
use App\Enums\Difficulty;
use App\Enums\QuestionScopeType;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionBank;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Question Form')]
class QuestionForm extends Component
{
    use AuthorizesRequests;

    public QuestionBank $questionBank;

    public ?Question $question = null;

    public string $topic = '';

    public string $scope_type = 'specific';

    /** @var list<int> */
    public array $course_topic_ids = [];

    /** @var list<int> */
    public array $cpmk_ids = [];

    public string $question_type = 'essay';

    public string $question_text = '';

    public string $expected_answer = '';

    public string $key_concepts = '';

    public string $difficulty = 'medium';

    public string $cognitive_level = 'C2';

    public float|string $max_score = 10;

    /** @var list<array{label:string,option_text:string,is_correct:bool}> */
    public array $options = [];

    public function mount(QuestionBank $questionBank, ?Question $question = null): void
    {
        $this->authorize('update', $questionBank);
        $this->questionBank = $questionBank;

        if ($question?->exists) {
            abort_unless($question->question_bank_id === $questionBank->id, 404);
            $this->question = $question->load(['options', 'courseTopics', 'cpmks']);
            $this->topic = $question->topic ?? '';
            $this->scope_type = $question->scope_type?->value ?? QuestionScopeType::Specific->value;
            $this->course_topic_ids = $question->courseTopics->pluck('id')->map(fn ($id) => (int) $id)->all();
            $this->cpmk_ids = $question->cpmks->pluck('id')->map(fn ($id) => (int) $id)->all();
            $this->question_type = $question->question_type->value;
            $this->question_text = $question->question_text;
            $this->expected_answer = $question->expected_answer ?? '';
            $this->key_concepts = is_array($question->key_concepts) ? implode(', ', $question->key_concepts) : '';
            $this->difficulty = $question->difficulty?->value ?? 'medium';
            $this->cognitive_level = $question->cognitive_level?->value ?? 'C2';
            $this->max_score = (float) $question->max_score;
            $this->options = $question->options->map(fn ($option): array => [
                'label' => $option->label ?? '',
                'option_text' => $option->option_text,
                'is_correct' => (bool) $option->is_correct,
            ])->values()->all();
        } else {
            $this->options = [
                ['label' => 'A', 'option_text' => '', 'is_correct' => false],
                ['label' => 'B', 'option_text' => '', 'is_correct' => false],
            ];
        }
    }

    public function addOption(): void
    {
        $labels = range('A', 'Z');
        $next = $labels[count($this->options)] ?? (string) (count($this->options) + 1);
        $this->options[] = ['label' => $next, 'option_text' => '', 'is_correct' => false];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    protected function rules(): array
    {
        return [
            'topic' => ['nullable', 'string', 'max:255'],
            'scope_type' => ['required', Rule::enum(QuestionScopeType::class)],
            'course_topic_ids' => ['array'],
            'course_topic_ids.*' => ['integer', 'exists:course_topics,id'],
            'cpmk_ids' => ['array'],
            'cpmk_ids.*' => ['integer', 'exists:cpmks,id'],
            'question_type' => ['required', Rule::enum(QuestionType::class)],
            'question_text' => ['required', 'string'],
            'expected_answer' => ['nullable', 'string'],
            'key_concepts' => ['nullable', 'string'],
            'difficulty' => ['required', Rule::enum(Difficulty::class)],
            'cognitive_level' => ['required', Rule::enum(CognitiveLevel::class)],
            'max_score' => ['required', 'numeric', 'min:0'],
            'options' => ['array'],
            'options.*.label' => ['nullable', 'string', 'max:10'],
            'options.*.option_text' => ['nullable', 'string'],
            'options.*.is_correct' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $this->authorize('update', $this->questionBank);

        $concepts = collect(explode(',', $data['key_concepts'] ?? ''))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        DB::transaction(function () use ($data, $concepts): void {
            $payload = [
                'question_bank_id' => $this->questionBank->id,
                'course_id' => $this->questionBank->course_id,
                'user_id' => Auth::id(),
                'topic' => $data['topic'] ?: null,
                'scope_type' => QuestionScopeType::from($data['scope_type']),
                'question_type' => QuestionType::from($data['question_type']),
                'question_text' => $data['question_text'],
                'expected_answer' => $data['expected_answer'] ?: null,
                'key_concepts' => $concepts ?: null,
                'difficulty' => Difficulty::from($data['difficulty']),
                'cognitive_level' => CognitiveLevel::from($data['cognitive_level']),
                'max_score' => $data['max_score'],
            ];

            if ($this->question) {
                $this->question->update($payload);
                $this->question->options()->delete();
                $question = $this->question;
            } else {
                $question = Question::query()->create($payload);
            }

            if (in_array($data['question_type'], [QuestionType::MultipleChoice->value, QuestionType::TrueFalse->value], true)) {
                foreach ($this->options as $index => $option) {
                    if (trim((string) $option['option_text']) === '') {
                        continue;
                    }

                    $question->options()->create([
                        'label' => $option['label'] ?: null,
                        'option_text' => $option['option_text'],
                        'is_correct' => (bool) $option['is_correct'],
                        'order_index' => $index,
                    ]);
                }
            }

            $question->courseTopics()->sync($data['course_topic_ids'] ?? []);
            $question->cpmks()->sync($data['cpmk_ids'] ?? []);
        });

        session()->flash('success', __('ui.flash.question_saved'));
        $this->redirect(route('question-banks.edit', $this->questionBank), navigate: true);
    }

    public function render()
    {
        $course = $this->questionBank->course;

        return view('livewire.question-banks.question-form', [
            'types' => QuestionType::cases(),
            'scopeTypes' => QuestionScopeType::cases(),
            'difficulties' => Difficulty::cases(),
            'cognitiveLevels' => CognitiveLevel::cases(),
            'courseTopics' => $course?->topics()->orderBy('week_number')->get() ?? collect(),
            'cpmks' => $course?->cpmks()->orderBy('order_index')->get() ?? collect(),
            'questions' => $this->questionBank->questions()->latest()->limit(20)->get(),
        ])->layoutData(['header' => $this->question ? __('ui.question_banks.edit_question') : __('ui.question_banks.add_question')]);
    }
}
