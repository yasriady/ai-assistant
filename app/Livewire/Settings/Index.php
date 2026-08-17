<?php

namespace App\Livewire\Settings;

use App\Services\Theme\UserTheme;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Settings')]
class Index extends Component
{
    public string $theme = 'default';

    public function mount(UserTheme $userTheme): void
    {
        $this->theme = $userTheme->current();
    }

    public function setTheme(string $theme, UserTheme $userTheme): void
    {
        $this->theme = $userTheme->set($userTheme->assertValid($theme), auth()->user());
        session()->flash('success', __('ui.settings.theme.saved'));
        $this->redirect(route('settings'), navigate: false);
    }

    public function render(UserTheme $userTheme)
    {
        return view('livewire.settings.index', [
            'themes' => $userTheme->options(),
        ])->layoutData(['header' => __('ui.settings.title')]);
    }
}
