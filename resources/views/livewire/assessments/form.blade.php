<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ $assessment ? __('ui.assessments.edit_title') : __('ui.assessments.create_title') }}</h1>
    </div>

    <form wire:submit="save" class="max-w-3xl space-y-5 rounded-xl border border-ink-200 bg-white p-6">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.title') }}</label>
            <input wire:model="title" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            @error('title') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.course') }}</label>
                <select wire:model="course_id" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">{{ __('ui.common.select_course') }}</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->code }} — {{ $course->name }}</option>
                    @endforeach
                </select>
                @error('course_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.type') }}</label>
                <select wire:model="type" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.rubric') }}</label>
                <select wire:model="rubric_id" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">{{ __('ui.common.optional') }}</option>
                    @foreach ($rubrics as $rubric)
                        <option value="{{ $rubric->id }}">{{ $rubric->name }} (v{{ $rubric->version }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.status') }}</label>
                <select wire:model="status" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="draft">{{ __('ui.status.draft') }}</option>
                    <option value="published">{{ __('ui.status.published') }}</option>
                    <option value="closed">{{ __('ui.status.closed') }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.due_at') }}</label>
                <input wire:model="due_at" type="datetime-local" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rubrics.max_score') }}</label>
                <input wire:model="max_score" type="number" step="0.01" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.description') }}</label>
            <textarea wire:model="description" rows="3" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.instructions') }}</label>
            <textarea wire:model="instructions" rows="4" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save') }}</button>
            <a href="{{ route('assessments.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.cancel') }}</a>
        </div>
    </form>
</div>
