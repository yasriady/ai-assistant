<?php

namespace App\Livewire\QuestionBanks;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Models\QuestionBank;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Question Banks')]
class Index extends Component
{
    use AuthorizesRequests;
    use ConfirmsDeletion;
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $bankId): void
    {
        $bank = QuestionBank::query()->findOrFail($bankId);
        $this->authorize('delete', $bank);
        $bank->delete();
        session()->flash('success', __('ui.flash.question_bank_deleted'));
    }

    public function render()
    {
        $user = Auth::user();

        $banks = QuestionBank::query()
            ->with('course')
            ->withCount('questions')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);

        return view('livewire.question-banks.index', compact('banks'))
            ->layoutData(['header' => __('ui.question_banks.title')]);
    }
}
