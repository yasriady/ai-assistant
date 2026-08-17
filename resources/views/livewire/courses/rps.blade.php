<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $course->code }} — {{ $course->name }}</div>
            <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ __('ui.rps.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.rps.subtitle') }}</p>
        </div>
        <a href="{{ route('courses.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back') }}</a>
        <a href="{{ route('courses.rps.generate', $course) }}" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.rps.generate.action') }}</a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-xl border border-ink-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.midterm_settings') }}</h2>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.rps.midterm_help') }}</p>
            <div class="mt-4 max-w-xs">
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rps.midterm_week') }}</label>
                <input wire:model="midterm_week" type="number" min="1" max="20" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('midterm_week') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="rounded-xl border border-ink-200 bg-white p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.cpmk_title') }}</h2>
                    <p class="mt-1 text-sm text-ink-500">{{ __('ui.rps.cpmk_subtitle') }}</p>
                </div>
                <button type="button" wire:click="addCpmk" class="text-sm text-brand-700 hover:underline">{{ __('ui.rps.add_cpmk') }}</button>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($cpmks as $index => $cpmk)
                    <div class="grid gap-3 rounded-lg border border-ink-100 bg-ink-50 p-4 md:grid-cols-[120px_1fr_auto]" wire:key="cpmk-{{ $index }}">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-ink-600">{{ __('ui.rps.cpmk_code') }}</label>
                            <input wire:model="cpmks.{{ $index }}.code" type="text" placeholder="CPMK-1" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm">
                            @error('cpmks.'.$index.'.code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-ink-600">{{ __('ui.common.description') }}</label>
                            <input wire:model="cpmks.{{ $index }}.description" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm">
                            @error('cpmks.'.$index.'.description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end">
                            <button type="button" wire:click="removeCpmk({{ $index }})" class="text-sm text-rose-700 hover:underline">{{ __('ui.actions.remove') }}</button>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-ink-200 px-4 py-8 text-center text-sm text-ink-500">{{ __('ui.rps.no_cpmk') }}</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-ink-200 bg-white p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.topics_title') }}</h2>
                    <p class="mt-1 text-sm text-ink-500">{{ __('ui.rps.topics_subtitle') }}</p>
                </div>
                <button type="button" wire:click="addTopic" class="text-sm text-brand-700 hover:underline">{{ __('ui.rps.add_topic') }}</button>
            </div>

            <div class="mt-4 space-y-4">
                @forelse ($topics as $index => $topic)
                    <div class="rounded-lg border border-ink-100 bg-ink-50 p-4" wire:key="topic-{{ $index }}">
                        <div class="grid gap-3 md:grid-cols-[100px_1fr_auto]">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-ink-600">{{ __('ui.rps.week') }}</label>
                                <input wire:model="topics.{{ $index }}.week_number" type="number" min="1" max="20" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm">
                                @error('topics.'.$index.'.week_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-ink-600">{{ __('ui.rps.topic_title') }}</label>
                                <input wire:model="topics.{{ $index }}.title" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm">
                                @error('topics.'.$index.'.title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex items-end">
                                <button type="button" wire:click="removeTopic({{ $index }})" class="text-sm text-rose-700 hover:underline">{{ __('ui.actions.remove') }}</button>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-medium text-ink-600">{{ __('ui.common.description') }}</label>
                            <textarea wire:model="topics.{{ $index }}.description" rows="2" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm"></textarea>
                        </div>

                        @if (count($cpmks) > 0)
                            <div class="mt-3">
                                <label class="mb-2 block text-xs font-medium text-ink-600">{{ __('ui.rps.linked_cpmk') }}</label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach ($cpmks as $cpmkIndex => $cpmk)
                                        @if ($cpmk['id'])
                                            <label class="flex items-center gap-2 rounded-md border border-ink-200 bg-white px-3 py-1.5 text-sm text-ink-700">
                                                <input type="checkbox" wire:model="topics.{{ $index }}.cpmk_ids" value="{{ $cpmk['id'] }}" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                                                {{ $cpmk['code'] }}
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                                @if (collect($cpmks)->every(fn ($c) => ! $c['id']))
                                    <p class="mt-2 text-xs text-amber-700">{{ __('ui.rps.save_cpmk_first') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-ink-200 px-4 py-8 text-center text-sm text-ink-500">{{ __('ui.rps.no_topics') }}</div>
                @endforelse
            </div>
        </section>

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save') }}</button>
        </div>
    </form>
</div>
