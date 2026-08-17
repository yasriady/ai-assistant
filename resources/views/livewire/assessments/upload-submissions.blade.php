<div>
    <div class="mb-6">
        <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $assessment->title }}</div>
        <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ __('ui.upload.title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ $namingHint }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-5 rounded-xl border border-ink-200 bg-white p-6">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.upload.files') }}</label>
            <input wire:model="files" type="file" multiple class="block w-full text-sm text-ink-600">
            @error('files') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            @error('files.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="files" class="mt-2 text-sm text-ink-500">{{ __('ui.common.uploading') }}</div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save_uploads') }}</button>
            <a href="{{ route('assessments.show', $assessment) }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.cancel') }}</a>
        </div>
    </form>
</div>
