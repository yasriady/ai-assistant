<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $assessment->title }}</div>
            <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ __('ui.exam_builder.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.exam_builder.subtitle') }}</p>
            <p class="mt-2 rounded-md bg-brand-50 px-3 py-2 text-xs text-brand-800">{{ __('ui.exam_builder.scope_hint', ['type' => $assessment->type->label()]) }}</p>
        </div>
        <a href="{{ route('assessments.show', $assessment) }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back_to_assessment') }}</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-ink-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.exam_builder.attached') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($assessment->examQuestions as $examQuestion)
                    <div class="rounded-lg border border-ink-100 p-3" wire:key="eq-{{ $examQuestion->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs text-ink-500">#{{ $examQuestion->order_index + 1 }} · {{ $examQuestion->question?->question_type?->label() }}</div>
                                <div class="mt-1 text-sm text-ink-900">{{ \Illuminate\Support\Str::limit($examQuestion->question?->question_text, 160) }}</div>
                            </div>
                            <div class="flex shrink-0 gap-2 text-xs">
                                <button type="button" wire:click="move({{ $examQuestion->id }}, 'up')" class="text-ink-600 hover:underline">{{ __('ui.actions.up') }}</button>
                                <button type="button" wire:click="move({{ $examQuestion->id }}, 'down')" class="text-ink-600 hover:underline">{{ __('ui.actions.down') }}</button>
                                <button type="button" wire:click="detach({{ $examQuestion->id }})" class="text-rose-700 hover:underline">{{ __('ui.actions.remove') }}</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-ink-200 px-4 py-8 text-center text-sm text-ink-500">{{ __('ui.exam_builder.empty_attached') }}</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-ink-200 bg-white p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.exam_builder.available') }}</h2>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('ui.common.search_placeholder') }}" class="w-48 rounded-md border border-ink-200 px-3 py-1.5 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($available as $question)
                    <div class="rounded-lg border border-ink-100 p-3">
                        <div class="text-xs text-ink-500">{{ $question->question_type?->label() }} · {{ $question->scope_type?->label() }} · {{ $question->difficulty?->label() }}</div>
                        <div class="mt-1 text-sm text-ink-900">{{ \Illuminate\Support\Str::limit($question->question_text, 160) }}</div>
                        <button type="button" wire:click="attach({{ $question->id }})" class="mt-2 text-sm text-brand-700 hover:underline">{{ __('ui.actions.attach') }}</button>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-ink-200 px-4 py-8 text-center text-sm text-ink-500">{{ __('ui.exam_builder.empty_available') }}</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
