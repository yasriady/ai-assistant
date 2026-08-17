<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $course->code }} — {{ $course->name }}</div>
            <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ __('ui.rps.generate.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.rps.generate.subtitle') }}</p>
        </div>
        <a href="{{ route('courses.rps', $course) }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back') }}</a>
    </div>

    @if ($step === 1)
        <form wire:submit="generate" class="space-y-6">
            <section class="rounded-xl border border-ink-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.generate.cpl_select') }}</h2>
                <p class="mt-1 text-sm text-ink-500">{{ __('ui.rps.generate.cpl_help') }}</p>

                @if ($cplOutcomes->isEmpty())
                    <p class="mt-4 text-sm text-amber-700">
                        {{ __('ui.cpl.empty') }}
                        <a href="{{ route('cpls.import') }}" class="text-brand-700 hover:underline">{{ __('ui.cpl.import') }}</a>
                    </p>
                @else
                    <div class="mt-4 max-h-72 space-y-2 overflow-y-auto rounded-lg border border-ink-100 bg-ink-50 p-3">
                        @foreach ($cplOutcomes as $cpl)
                            <label class="flex items-start gap-2 text-sm text-ink-700">
                                <input type="checkbox" wire:model="cpl_outcome_ids" value="{{ $cpl->id }}" class="mt-0.5 rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                                <span>
                                    <span class="font-medium">{{ $cpl->code }}</span>
                                    <span class="text-ink-400">({{ $cpl->official_code }})</span>
                                    — {{ \Illuminate\Support\Str::limit($cpl->description, 120) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                @error('cpl_outcome_ids') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </section>

            <section class="rounded-xl border border-ink-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.generate.settings') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rps.generate.total_weeks') }}</label>
                        <input wire:model="total_weeks" type="number" min="4" max="20" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rps.midterm_week') }}</label>
                        <input wire:model="midterm_week" type="number" min="2" max="19" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm">
                        @error('midterm_week') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rps.generate.notes') }}</label>
                    <textarea wire:model="teaching_notes" rows="3" placeholder="{{ __('ui.rps.generate.notes_placeholder') }}" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm"></textarea>
                </div>
            </section>

            <section class="rounded-xl border border-ink-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.generate.reference') }}</h2>
                <p class="mt-1 text-sm text-ink-500">{{ __('ui.rps.generate.reference_help') }}</p>
                <div class="mt-4">
                    <input wire:model="reference" type="file" accept=".pdf,.docx,.txt" class="block w-full text-sm text-ink-600">
                    @error('reference') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="reference" class="mt-2 text-sm text-ink-500">{{ __('ui.common.uploading') }}</div>
                </div>
            </section>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="generate">{{ __('ui.rps.generate.submit') }}</span>
                    <span wire:loading wire:target="generate">{{ __('ui.rps.generate.working') }}</span>
                </button>
            </div>
        </form>
    @else
        <div class="space-y-6">
            <div class="rounded-md border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
                {{ __('ui.rps.generate.preview_hint') }}
            </div>

            <section class="rounded-xl border border-ink-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.cpmk_title') }}</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($draft['cpmks'] ?? [] as $cpmk)
                        <div class="rounded-lg border border-ink-100 bg-ink-50 p-3 text-sm">
                            <div class="font-medium text-ink-900">{{ $cpmk['code'] }}</div>
                            <div class="mt-1 text-ink-700">{{ $cpmk['description'] }}</div>
                            @if (! empty($cpmk['cpl_codes']))
                                <div class="mt-2 text-xs text-ink-500">CPL: {{ implode(', ', $cpmk['cpl_codes']) }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-ink-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.rps.topics_title') }}</h2>
                <div class="mt-4 space-y-2">
                    @foreach ($draft['topics'] ?? [] as $topic)
                        <div class="rounded-lg border border-ink-100 p-3 text-sm">
                            <div class="font-medium text-ink-900">{{ __('ui.rps.week') }} {{ $topic['week_number'] }} — {{ $topic['title'] }}</div>
                            @if (! empty($topic['description']))
                                <div class="mt-1 text-ink-600">{{ $topic['description'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex gap-3">
                <button type="button" wire:click="approve" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">
                    {{ __('ui.rps.generate.approve') }}
                </button>
                <button type="button" wire:click="back" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">
                    {{ __('ui.rps.generate.regenerate') }}
                </button>
            </div>
        </div>
    @endif
</div>
