<?php

namespace App\Livewire\Cpls;

use App\Enums\CplCategory;
use App\Models\CplOutcome;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('CPL')]
class Index extends Component
{
    public string $search = '';

    public string $category = '';

    public function render()
    {
        $outcomes = CplOutcome::query()
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('code', 'like', $term)
                        ->orWhere('official_code', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->orderBy('order_index')
            ->get();

        return view('livewire.cpls.index', [
            'outcomes' => $outcomes,
            'categories' => CplCategory::cases(),
        ])->layoutData(['header' => __('ui.cpl.title')]);
    }
}
