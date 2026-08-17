<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.ai_settings.title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ __('ui.ai_settings.page_subtitle') }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-5 rounded-xl border border-ink-200 bg-white p-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.ai_settings.provider') }}</label>
                <select wire:model="provider" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="openai">OpenAI</option>
                    <option value="gemini">Gemini</option>
                    <option value="ollama">Ollama</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.ai_settings.model') }}</label>
                <input wire:model="model" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.ai_settings.temperature') }}</label>
                <input wire:model="temperature" type="number" step="0.01" min="0" max="2" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.ai_settings.max_tokens') }}</label>
                <input wire:model="max_tokens" type="number" min="1" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.ai_settings.api_key') }}</label>
                <input wire:model="api_key" type="password" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.ai_settings.base_url') }}</label>
                <input wire:model="base_url" type="text" placeholder="{{ __('ui.ai_settings.base_url_placeholder') }}" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm text-ink-700">
                    <input wire:model="is_active" type="checkbox" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                    {{ __('ui.ai_settings.active_config') }}
                </label>
            </div>
        </div>

        <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save_settings') }}</button>
    </form>

    @if ($settings->isNotEmpty())
        <div class="mt-8 overflow-hidden rounded-xl border border-ink-200 bg-white">
            <div class="border-b border-ink-100 px-5 py-4 text-sm font-semibold text-ink-900">{{ __('ui.ai_settings.saved_configs') }}</div>
            <table class="min-w-full divide-y divide-ink-100 text-sm">
                <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('ui.common.provider') }}</th>
                        <th class="px-4 py-3">{{ __('ui.common.model') }}</th>
                        <th class="px-4 py-3">{{ __('ui.common.active') }}</th>
                        <th class="px-4 py-3">{{ __('ui.common.updated') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($settings as $setting)
                        <tr>
                            <td class="px-4 py-3">{{ $setting->provider }}</td>
                            <td class="px-4 py-3">{{ $setting->model }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$setting->is_active ? 'active' : 'draft'" /></td>
                            <td class="px-4 py-3 text-ink-500">{{ $setting->updated_at?->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
