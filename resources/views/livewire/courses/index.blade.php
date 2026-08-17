<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.courses.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.courses.subtitle') }}</p>
        </div>
        <a href="{{ route('courses.create') }}" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.courses.new') }}</a>
    </div>

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('ui.courses.search') }}" class="w-full max-w-md rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
    </div>

    <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.common.code') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.name') }}</th>
                    <th class="px-4 py-3">{{ __('ui.term.code') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.class') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.students') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.assessments') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($courses as $course)
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $course->code }}</td>
                        <td class="px-4 py-3">{{ $course->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $course->term_code }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $course->class_name ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $course->students_count }}</td>
                        <td class="px-4 py-3">{{ $course->assessments_count }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$course->is_active ? 'active' : 'draft'" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('courses.rps', $course) }}" class="text-brand-700 hover:underline">{{ __('ui.rps.manage') }}</a>
                            <a href="{{ route('courses.edit', $course) }}" class="ml-3 text-brand-700 hover:underline">{{ __('ui.actions.edit') }}</a>
                            <button type="button" wire:click="askDelete({{ $course->id }}, @js($course->code.' — '.$course->name))" class="ml-3 text-rose-700 hover:underline">{{ __('ui.actions.delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-ink-500">{{ __('ui.courses.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $courses->links() }}</div>

    @include('livewire.partials.confirm-delete-modal')
</div>
