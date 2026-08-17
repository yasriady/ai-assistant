<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $questionBank->name }}</div>
            <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ $question ? __('ui.question_banks.edit_question') : __('ui.question_banks.add_question') }}</h1>
        </div>
        <a href="{{ route('question-banks.index') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back_to_banks') }}</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <form wire:submit="save" class="space-y-5 rounded-xl border border-ink-200 bg-white p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.type') }}</label>
                    <select wire:model.live="question_type" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.question_banks.scope_type') }}</label>
                    <select wire:model.live="scope_type" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($scopeTypes as $scope)
                            <option value="{{ $scope->value }}">{{ $scope->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.topic') }} <span class="font-normal text-ink-400">({{ __('ui.common.optional') }})</span></label>
                    <input wire:model="topic" type="text" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.difficulty') }}</label>
                    <select wire:model="difficulty" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($difficulties as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.cognitive_level') }}</label>
                    <select wire:model="cognitive_level" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($cognitiveLevels as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.question_banks.max_score') }}</label>
                    <input wire:model="max_score" type="number" step="0.01" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.common.key_concepts') }}</label>
                    <input wire:model="key_concepts" type="text" placeholder="{{ __('ui.common.comma_separated') }}" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
            </div>

            @if ($scope_type === 'specific' || $scope_type === 'case_study')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rps.topics_title') }}</label>
                    @if ($courseTopics->isEmpty())
                        <p class="text-sm text-amber-700">
                            {{ __('ui.rps.no_topics') }}
                            @if ($questionBank->course_id)
                                <a href="{{ route('courses.rps', $questionBank->course_id) }}" class="text-brand-700 hover:underline">{{ __('ui.rps.manage_link') }}</a>
                            @endif
                        </p>
                    @else
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($courseTopics as $courseTopic)
                                <label class="flex items-start gap-2 rounded-md border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-700">
                                    <input type="checkbox" wire:model="course_topic_ids" value="{{ $courseTopic->id }}" class="mt-0.5 rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                                    <span>
                                        <span class="font-medium">{{ __('ui.rps.week') }} {{ $courseTopic->week_number }}</span>
                                        — {{ $courseTopic->title }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if ($cpmks->isNotEmpty())
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.rps.cpmk_title') }}</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($cpmks as $cpmk)
                            <label class="flex items-center gap-2 rounded-md border border-ink-100 bg-ink-50 px-3 py-1.5 text-sm text-ink-700">
                                <input type="checkbox" wire:model="cpmk_ids" value="{{ $cpmk->id }}" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                                {{ $cpmk->code }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.question_banks.question_text') }}</label>
                <textarea wire:model="question_text" rows="5" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                @error('question_text') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.question_banks.expected_answer') }}</label>
                <textarea wire:model="expected_answer" rows="4" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
            </div>

            @if (in_array($question_type, ['multiple_choice', 'true_false'], true))
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.question_banks.options') }}</h2>
                        <button type="button" wire:click="addOption" class="text-sm text-brand-700 hover:underline">{{ __('ui.actions.add_option') }}</button>
                    </div>
                    @foreach ($options as $index => $option)
                        <div class="grid gap-2 rounded-lg border border-ink-100 bg-ink-50 p-3 sm:grid-cols-[70px_1fr_auto_auto]" wire:key="opt-{{ $index }}">
                            <input wire:model="options.{{ $index }}.label" placeholder="A" class="rounded-md border border-ink-200 px-2 py-1.5 text-sm">
                            <input wire:model="options.{{ $index }}.option_text" placeholder="{{ __('ui.question_banks.option_text') }}" class="rounded-md border border-ink-200 px-3 py-1.5 text-sm">
                            <label class="flex items-center gap-2 text-sm text-ink-700">
                                <input type="checkbox" wire:model="options.{{ $index }}.is_correct" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                                {{ __('ui.question_banks.correct') }}
                            </label>
                            <button type="button" wire:click="removeOption({{ $index }})" class="text-sm text-rose-700">{{ __('ui.actions.remove') }}</button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.save_question') }}</button>
            </div>
        </form>

        <aside class="rounded-xl border border-ink-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.question_banks.recent_questions') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($questions as $item)
                    <a href="{{ route('question-banks.questions.edit', [$questionBank, $item]) }}" class="block rounded-lg border border-ink-100 p-3 hover:bg-ink-50">
                        <div class="text-xs text-ink-500">{{ $item->question_type?->label() }}</div>
                        <div class="mt-1 text-sm text-ink-900">{{ \Illuminate\Support\Str::limit($item->question_text, 120) }}</div>
                    </a>
                @empty
                    <div class="text-sm text-ink-500">{{ __('ui.question_banks.no_questions') }}</div>
                @endforelse
            </div>
        </aside>
    </div>
</div>
