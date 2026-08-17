<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ $rubric ? __('ui.rubrics.edit_title') : __('ui.rubrics.create_title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ __('ui.rubrics.form_subtitle') }}</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="max-w-4xl space-y-4 rounded-xl border border-ink-200 bg-white p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.name') }}</label>
                    <input wire:model="name" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.course_optional') }}</label>
                    <select wire:model="course_id" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">{{ __('ui.common.template_global') }}</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->code }} — {{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input wire:model="is_template" type="checkbox" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                        {{ __('ui.rubrics.save_as_template') }}
                    </label>
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.description') }}</label>
                <textarea wire:model="description" rows="3" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
            </div>
        </div>

        @foreach ($criteria as $cIndex => $criterion)
            <div class="rounded-xl border border-ink-200 bg-white p-6" wire:key="criterion-{{ $cIndex }}">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rubrics.criterion_n', ['n' => $cIndex + 1]) }}</h2>
                    @if (count($criteria) > 1)
                        <button type="button" wire:click="removeCriterion({{ $cIndex }})" class="text-sm text-rose-700 hover:underline">{{ __('ui.actions.remove') }}</button>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.name') }}</label>
                        <input wire:model="criteria.{{ $cIndex }}.name" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('criteria.'.$cIndex.'.name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rubrics.weight_pct') }}</label>
                            <input wire:model="criteria.{{ $cIndex }}.weight" type="number" step="0.01" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rubrics.max_score') }}</label>
                            <input wire:model="criteria.{{ $cIndex }}.max_score" type="number" step="0.01" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.description') }}</label>
                        <textarea wire:model="criteria.{{ $cIndex }}.description" rows="2" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-500">{{ __('ui.rubrics.levels') }}</h3>
                        <button type="button" wire:click="addLevel({{ $cIndex }})" class="text-sm text-brand-700 hover:underline">{{ __('ui.actions.add_level') }}</button>
                    </div>

                    @foreach ($criterion['levels'] as $lIndex => $level)
                        <div class="grid gap-3 rounded-lg border border-ink-100 bg-ink-50 p-3 sm:grid-cols-4" wire:key="level-{{ $cIndex }}-{{ $lIndex }}">
                            <input wire:model="criteria.{{ $cIndex }}.levels.{{ $lIndex }}.name" placeholder="{{ __('ui.rubrics.level_name') }}" class="rounded-md border border-ink-200 px-3 py-2 text-sm">
                            <input wire:model="criteria.{{ $cIndex }}.levels.{{ $lIndex }}.min_score" type="number" step="0.01" placeholder="{{ __('ui.common.min') }}" class="rounded-md border border-ink-200 px-3 py-2 text-sm">
                            <input wire:model="criteria.{{ $cIndex }}.levels.{{ $lIndex }}.max_score" type="number" step="0.01" placeholder="{{ __('ui.common.max') }}" class="rounded-md border border-ink-200 px-3 py-2 text-sm">
                            <div class="flex gap-2">
                                <input wire:model="criteria.{{ $cIndex }}.levels.{{ $lIndex }}.description" placeholder="{{ __('ui.common.description') }}" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm">
                                <button type="button" wire:click="removeLevel({{ $cIndex }}, {{ $lIndex }})" class="text-xs text-rose-700">×</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="addCriterion" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.add_criterion') }}</button>
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save_rubric') }}</button>
            <a href="{{ route('rubrics.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.cancel') }}</a>
        </div>
    </form>
</div>
