<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $assessment->title }}</div>
            <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ __('ui.analytics.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ $assessment->course?->name }}</p>
        </div>
        <a href="{{ route('assessments.show', $assessment) }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.back') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            [__('ui.analytics.submissions'), $summary['total']],
            [__('ui.analytics.finalized'), $summary['finalized']],
            [__('ui.common.average'), $summary['average'] !== null ? number_format($summary['average'], 2) : '—'],
            [__('ui.common.median'), $summary['median'] !== null ? number_format($summary['median'], 2) : '—'],
            [__('ui.common.min'), $summary['min'] !== null ? number_format($summary['min'], 2) : '—'],
            [__('ui.common.max'), $summary['max'] !== null ? number_format($summary['max'], 2) : '—'],
        ] as [$label, $value])
            <div class="rounded-xl border border-ink-200 bg-white px-5 py-4">
                <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $label }}</div>
                <div class="mt-2 text-2xl font-semibold text-ink-900">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-ink-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.analytics.status_breakdown') }}</h2>
            <div class="mt-4 space-y-2">
                @forelse ($statusBreakdown as $status => $count)
                    <div class="flex items-center justify-between rounded-md border border-ink-100 px-3 py-2 text-sm">
                        <x-status-badge :status="$status" />
                        <span class="font-medium text-ink-900">{{ $count }}</span>
                    </div>
                @empty
                    <div class="text-sm text-ink-500">{{ __('ui.common.no_data') }}</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-ink-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.analytics.per_question') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($questionStats as $stat)
                    <div class="rounded-lg border border-ink-100 p-3">
                        <div class="text-sm text-ink-900">{{ \Illuminate\Support\Str::limit($stat->question_text, 120) }}</div>
                        <div class="mt-1 text-xs text-ink-500">
                            {{ __('ui.analytics.avg_score') }}: {{ number_format((float) $stat->avg_score, 2) }} · {{ __('ui.analytics.answers') }}: {{ $stat->answer_count }}
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-ink-500">{{ __('ui.analytics.no_question_data') }}</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
