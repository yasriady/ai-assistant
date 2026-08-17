<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.students.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.students.subtitle') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('students.import') }}" class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ __('ui.students.import') }}</a>
            <a href="{{ route('students.create') }}" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.students.new') }}</a>
        </div>
    </div>

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('ui.students.search') }}" class="w-full max-w-md rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
    </div>

    <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.common.nim') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.name') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.program') }}</th>
                    <th class="px-4 py-3">{{ __('ui.nav.courses') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($students as $student)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $student->nim }}</td>
                        <td class="px-4 py-3">
                            <div>{{ $student->name }}</div>
                            <div class="text-xs text-ink-500">{{ $student->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $student->program ?: '—' }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $student->courses->pluck('code')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('students.edit', $student) }}" class="text-brand-700 hover:underline">{{ __('ui.actions.edit') }}</a>
                            <button type="button" wire:click="askDelete({{ $student->id }}, @js($student->nim.' — '.$student->name))" class="ml-3 text-rose-700 hover:underline">{{ __('ui.actions.delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-ink-500">{{ __('ui.students.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $students->links() }}</div>

    @include('livewire.partials.confirm-delete-modal')
</div>
