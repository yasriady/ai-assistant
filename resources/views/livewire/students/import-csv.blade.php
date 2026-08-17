<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.students.import_title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ __('ui.students.import_hint') }}</p>
    </div>

    <form wire:submit="import" class="max-w-2xl space-y-5 rounded-xl border border-ink-200 bg-white p-6">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.students.attach_course') }}</label>
            <select wire:model="course_id" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">{{ __('ui.common.none') }}</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->code }} — {{ $course->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.students.csv_file') }}</label>
            <input wire:model="csv" type="file" accept=".csv,text/csv" class="block w-full text-sm text-ink-600">
            @error('csv') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="csv" class="mt-2 text-sm text-ink-500">{{ __('ui.common.uploading') }}</div>
        </div>

        @if ($resultMessage)
            <div class="rounded-md border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">{{ $resultMessage }}</div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.import') }}</button>
            <a href="{{ route('students.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back') }}</a>
        </div>
    </form>
</div>
