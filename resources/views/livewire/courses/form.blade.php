<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ $course ? __('ui.courses.edit_title') : __('ui.courses.create_title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ __('ui.courses.form_subtitle') }}</p>
    </div>

    <form wire:submit="save" class="max-w-3xl space-y-5 rounded-xl border border-ink-200 bg-white p-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.code') }}</label>
                <input wire:model="code" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.name') }}</label>
                <input wire:model="name" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.term.code') }}</label>
                <select wire:model="term_code" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @foreach ($termChoices as $code)
                        <option value="{{ $code }}">{{ \App\Support\AcademicTerm::label($code) }}</option>
                    @endforeach
                </select>
                @error('term_code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.class') }}</label>
                <input wire:model="class_name" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm text-ink-700">
                    <input wire:model="is_active" type="checkbox" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                    {{ __('ui.common.active') }}
                </label>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.description') }}</label>
            <textarea wire:model="description" rows="4" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save') }}</button>
            <a href="{{ route('courses.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.cancel') }}</a>
        </div>
    </form>
</div>
