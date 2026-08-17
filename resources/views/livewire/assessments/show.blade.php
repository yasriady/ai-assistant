<div>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $assessment->course?->code }} · {{ $assessment->type?->label() }}</div>
            <h1 class="mt-1 text-2xl font-semibold text-ink-900">{{ $assessment->title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-status-badge :status="$assessment->status" />
                <span class="text-xs text-ink-500">{{ __('ui.common.engine') }}: {{ $assessment->engine?->label() }}</span>
                <span class="text-xs text-ink-500">{{ __('ui.common.max') }}: {{ $assessment->max_score }}</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('assessments.edit', $assessment) }}" class="rounded-md border border-ink-200 px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.edit') }}</a>
            <a href="{{ route('assessments.upload', $assessment) }}" class="rounded-md border border-ink-200 px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.actions.upload') }}</a>
            @if ($assessment->engine?->value === 'exam')
                <a href="{{ route('assessments.exam-builder', $assessment) }}" class="rounded-md border border-ink-200 px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.assessments.exam_builder') }}</a>
            @endif
            <a href="{{ route('assessments.analytics', $assessment) }}" class="rounded-md border border-ink-200 px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.assessments.analytics') }}</a>
            <a href="{{ route('assessments.export', $assessment) }}" class="rounded-md bg-brand-700 px-3 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.actions.export_csv') }}</a>
        </div>
    </div>

    @if ($assessment->description || $assessment->instructions)
        <div class="mb-6 rounded-xl border border-ink-200 bg-white p-5 text-sm text-ink-700">
            @if ($assessment->description)
                <p>{{ $assessment->description }}</p>
            @endif
            @if ($assessment->instructions)
                <p class="mt-3 whitespace-pre-line text-ink-600">{{ $assessment->instructions }}</p>
            @endif
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
        <div class="border-b border-ink-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.common.submissions') }}</h2>
        </div>
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.common.student') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.status') }}</th>
                    <th class="px-4 py-3">{{ __('ui.review.ai_score') }}</th>
                    <th class="px-4 py-3">{{ __('ui.review.final_score') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($assessment->submissions as $submission)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $submission->student?->name }}</div>
                            <div class="text-xs text-ink-500">{{ $submission->student?->nim }}</div>
                        </td>
                        <td class="px-4 py-3"><x-status-badge :status="$submission->status" /></td>
                        <td class="px-4 py-3">{{ $submission->ai_score ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $submission->final_score ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('assessments.review', [$assessment, $submission]) }}" class="text-brand-700 hover:underline">{{ __('ui.actions.review') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-ink-500">{{ __('ui.assessments.no_submissions') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($assessment->examQuestions->isNotEmpty())
        <div class="mt-6 rounded-xl border border-ink-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.assessments.exam_questions') }}</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-ink-700">
                @foreach ($assessment->examQuestions as $examQuestion)
                    <li>{{ \Illuminate\Support\Str::limit($examQuestion->question?->question_text, 140) }}</li>
                @endforeach
            </ol>
        </div>
    @endif
</div>
