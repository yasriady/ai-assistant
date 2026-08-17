<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.dashboard.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.dashboard.subtitle') }}</p>
        </div>
        <a href="{{ route('assessments.create') }}" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.dashboard.new_assessment') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            [__('ui.dashboard.courses'), $stats['courses']],
            [__('ui.dashboard.assessments'), $stats['assessments']],
            [__('ui.dashboard.students'), $stats['students']],
            [__('ui.dashboard.pending_reviews'), $stats['pending_reviews']],
        ] as [$label, $value])
            <div class="rounded-xl border border-ink-200 bg-white px-5 py-4">
                <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $label }}</div>
                <div class="mt-2 text-3xl font-semibold text-ink-900">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-ink-200 bg-white">
            <div class="border-b border-ink-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.dashboard.recent_assessments') }}</h2>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse ($recentAssessments as $assessment)
                    <a href="{{ route('assessments.show', $assessment) }}" class="block px-5 py-3 hover:bg-ink-50">
                        <div class="font-medium text-ink-900">{{ $assessment->title }}</div>
                        <div class="mt-1 text-xs text-ink-500">
                            {{ $assessment->course?->name }} · {{ $assessment->type?->label() }} ·
                            <x-status-badge :status="$assessment->status" class="ml-1 align-middle" />
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-sm text-ink-500">{{ __('ui.dashboard.no_assessments') }}</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-ink-200 bg-white">
            <div class="border-b border-ink-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.dashboard.recent_submissions') }}</h2>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse ($recentSubmissions as $submission)
                    <a href="{{ route('assessments.review', [$submission->assessment_id, $submission]) }}" class="block px-5 py-3 hover:bg-ink-50">
                        <div class="font-medium text-ink-900">{{ $submission->student?->name }}</div>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-ink-500">
                            <span>{{ $submission->assessment?->title }}</span>
                            <x-status-badge :status="$submission->status" />
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-sm text-ink-500">{{ __('ui.dashboard.no_submissions') }}</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
