<div>
    <div class="mb-6">
        <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $assessment->title }}</div>
        <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ __('ui.review.title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">
            {{ $submission->student?->name }} ({{ $submission->student?->nim }}) ·
            <x-status-badge :status="$submission->status" />
        </p>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="space-y-4 rounded-xl border border-ink-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.review.ai_suggestion') }}</h2>
            <p class="text-xs text-ink-500">{{ __('ui.review.ai_disclaimer') }}</p>

            @if ($latestAi)
                <div class="rounded-lg border border-ink-100 bg-ink-50 p-4">
                    <div class="text-sm text-ink-600">{{ __('ui.review.suggested_score') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-ink-900">{{ $latestAi->score ?? '—' }} <span class="text-base font-normal text-ink-500">/ {{ $latestAi->max_score ?? $assessment->max_score }}</span></div>
                    <div class="mt-2 text-xs text-ink-500">{{ __('ui.review.confidence') }}: {{ $latestAi->confidence ?? __('ui.review.na') }} · {{ $latestAi->provider }} / {{ $latestAi->model }}</div>
                </div>

                @if ($latestAi->overall_feedback)
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-ink-500">{{ __('ui.review.overall_feedback') }}</div>
                        <p class="mt-2 whitespace-pre-line text-sm text-ink-700">{{ $latestAi->overall_feedback }}</p>
                    </div>
                @endif

                @if ($latestAi->items->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($latestAi->items as $item)
                            <div class="rounded-lg border border-ink-100 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-medium text-ink-900">{{ $item->criterion_name }}</div>
                                    <div class="text-sm text-ink-600">{{ $item->score }} / {{ $item->max_score }}</div>
                                </div>
                                @if ($item->evidence)
                                    <p class="mt-2 text-xs text-ink-500"><span class="font-medium text-ink-700">{{ __('ui.review.evidence') }}:</span> {{ $item->evidence }}</p>
                                @endif
                                @if ($item->reasoning)
                                    <p class="mt-1 text-xs text-ink-500"><span class="font-medium text-ink-700">{{ __('ui.review.reasoning') }}:</span> {{ $item->reasoning }}</p>
                                @endif
                                @if ($item->insufficient_evidence)
                                    <p class="mt-2 text-xs font-medium text-amber-700">{{ __('ui.review.insufficient_evidence') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="rounded-lg border border-dashed border-ink-200 px-4 py-8 text-center text-sm text-ink-500">
                    {{ __('ui.review.no_ai_result') }}
                </div>
            @endif

            @if ($submission->files->isNotEmpty())
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-500">{{ __('ui.common.files') }}</div>
                    <ul class="mt-2 space-y-2 text-sm">
                        @foreach ($submission->files as $file)
                            <li class="flex items-center justify-between gap-3 rounded-md border border-ink-100 px-3 py-2">
                                <span>{{ $file->original_name }}</span>
                                <a href="{{ URL::temporarySignedRoute('files.download', now()->addMinutes(30), ['file' => $file->id]) }}" class="text-brand-700 hover:underline">{{ __('ui.actions.download') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        <section class="rounded-xl border border-ink-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.review.lecturer_decision') }}</h2>
            <form class="mt-4 space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.review.final_score') }}</label>
                    <input wire:model="final_score" type="number" step="0.01" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('final_score') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.review.feedback_to_student') }}</label>
                    <textarea wire:model="feedback" rows="5" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.review.internal_notes') }}</label>
                    <textarea wire:model="lecturer_notes" rows="3" class="w-full rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="button" wire:click="markReviewed" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.mark_reviewed') }}</button>
                    <button type="button" wire:click="finalize" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.finalize_score') }}</button>
                    <a href="{{ route('assessments.show', $assessment) }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back') }}</a>
                </div>
            </form>
        </section>
    </div>
</div>
