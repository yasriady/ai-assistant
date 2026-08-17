<?php

namespace App\Livewire\QuestionBanks;

use App\Enums\QuestionBankPurpose;
use App\Models\Course;
use App\Models\QuestionBank;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Question Bank Form')]
class Form extends Component
{
    use AuthorizesRequests;

    public ?QuestionBank $questionBank = null;

    public ?int $course_id = null;

    public string $name = '';

    public string $description = '';

    public ?string $purpose = null;

    public function mount(?QuestionBank $questionBank = null): void
    {
        if ($questionBank?->exists) {
            $this->authorize('update', $questionBank);
            $this->questionBank = $questionBank;
            $this->fill($questionBank->only(['course_id', 'name', 'description', 'purpose']));
            $this->purpose = $questionBank->purpose?->value;
        } else {
            $this->authorize('create', QuestionBank::class);
        }
    }

    protected function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'purpose' => ['nullable', Rule::enum(QuestionBankPurpose::class)],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->questionBank) {
            $this->authorize('update', $this->questionBank);
            $this->questionBank->update($data);
            session()->flash('success', __('ui.flash.question_bank_updated'));
            $this->redirect(route('question-banks.questions.create', $this->questionBank), navigate: true);
        } else {
            $this->authorize('create', QuestionBank::class);
            $bank = QuestionBank::query()->create([
                ...$data,
                'user_id' => Auth::id(),
            ]);
            session()->flash('success', __('ui.flash.question_bank_created'));
            $this->redirect(route('question-banks.questions.create', $bank), navigate: true);
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

        return view('livewire.question-banks.form', compact('courses'))
            ->layoutData(['header' => $this->questionBank ? __('ui.question_banks.edit_title') : __('ui.question_banks.create_title')]);
    }
}
