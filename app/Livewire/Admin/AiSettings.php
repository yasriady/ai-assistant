<?php

namespace App\Livewire\Admin;

use App\Models\AiSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('AI Settings')]
class AiSettings extends Component
{
    public ?int $settingId = null;

    public string $provider = 'openai';

    public string $model = 'gpt-4o-mini';

    public float|string $temperature = 0.2;

    public int|string $max_tokens = 2000;

    public bool $is_active = true;

    public string $api_key = '';

    public string $base_url = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $setting = AiSetting::query()->latest('id')->first();

        if ($setting) {
            $this->settingId = $setting->id;
            $this->provider = $setting->provider;
            $this->model = $setting->model;
            $this->temperature = (float) $setting->temperature;
            $this->max_tokens = (int) $setting->max_tokens;
            $this->is_active = (bool) $setting->is_active;
            $this->api_key = (string) data_get($setting->config, 'api_key', '');
            $this->base_url = (string) data_get($setting->config, 'base_url', '');
        }
    }

    protected function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:openai,gemini,ollama'],
            'model' => ['required', 'string', 'max:255'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'api_key' => ['nullable', 'string'],
            'base_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $data = $this->validate();

        $payload = [
            'provider' => $data['provider'],
            'model' => $data['model'],
            'temperature' => $data['temperature'],
            'max_tokens' => $data['max_tokens'],
            'is_active' => $data['is_active'],
            'config' => [
                'api_key' => $data['api_key'] ?: null,
                'base_url' => $data['base_url'] ?: null,
            ],
        ];

        if ($this->settingId) {
            AiSetting::query()->whereKey($this->settingId)->update($payload);
        } else {
            $setting = AiSetting::query()->create($payload);
            $this->settingId = $setting->id;
        }

        session()->flash('success', __('ui.flash.ai_settings_saved'));
    }

    public function render()
    {
        $settings = AiSetting::query()->latest('id')->limit(10)->get();

        return view('livewire.admin.ai-settings', compact('settings'))
            ->layoutData(['header' => __('ui.ai_settings.title')]);
    }
}
