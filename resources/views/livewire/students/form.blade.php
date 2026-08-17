<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ $student ? __('ui.students.edit_title') : __('ui.students.create_title') }}</h1>
    </div>

    <form wire:submit="save" class="max-w-3xl space-y-5 rounded-xl border border-ink-200 bg-white p-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.nim') }}</label>
                <input wire:model="nim" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('nim') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.name') }}</label>
                <input wire:model="name" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.email') }}</label>
                <input wire:model="email" type="email" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.program') }}</label>
                <input wire:model="program" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.class') }}</label>
                <input wire:model="class_name" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.students.enrolled_courses') }}</label>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($courses as $course)
                    <label class="flex items-center gap-2 rounded-md border border-ink-200 px-3 py-2 text-sm">
                        <input type="checkbox" wire:model="course_ids" value="{{ $course->id }}" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                        {{ $course->code }} — {{ $course->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save') }}</button>
            <a href="{{ route('students.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.cancel') }}</a>
        </div>
    </form>
</div>
