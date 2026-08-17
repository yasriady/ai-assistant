<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.cpl.import_title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ __('ui.cpl.import_hint') }}</p>
    </div>

    <form wire:submit="import" class="max-w-2xl space-y-5 rounded-xl border border-ink-200 bg-white p-6">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.cpl.program') }}</label>
            <input wire:model="program" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            @error('program') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.cpl.docx_file') }}</label>
            <input wire:model="document" type="file" accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="block w-full text-sm text-ink-600">
            @error('document') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="document" class="mt-2 text-sm text-ink-500">{{ __('ui.common.uploading') }}</div>
            <p class="mt-2 text-xs text-ink-500">{{ __('ui.cpl.import_format') }}</p>
        </div>

        <label class="flex items-start gap-2 text-sm text-ink-700">
            <input type="checkbox" wire:model="replace_missing" class="mt-0.5 rounded border-ink-300 text-brand-700 focus:ring-brand-600">
            <span>{{ __('ui.cpl.replace_missing') }}</span>
        </label>

        @if ($resultMessage)
            <div class="rounded-md border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">{{ $resultMessage }}</div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.import') }}</button>
            <a href="{{ route('cpls.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back') }}</a>
        </div>
    </form>
</div>
