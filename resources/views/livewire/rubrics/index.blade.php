<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.rubrics.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.rubrics.subtitle') }}</p>
        </div>
        <a href="{{ route('rubrics.create') }}" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.rubrics.new') }}</a>
    </div>

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('ui.rubrics.search') }}" class="w-full max-w-md rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
    </div>

    <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.common.name') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.course') }}</th>
                    <th class="px-4 py-3">{{ __('ui.rubrics.criteria') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.version') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($rubrics as $rubric)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $rubric->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $rubric->course?->name ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $rubric->criteria_count }}</td>
                        <td class="px-4 py-3">v{{ $rubric->version }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('rubrics.edit', $rubric) }}" class="text-brand-700 hover:underline">{{ __('ui.actions.edit') }}</a>
                            <button type="button" wire:click="askDelete({{ $rubric->id }}, @js($rubric->name))" class="ml-3 text-rose-700 hover:underline">{{ __('ui.actions.delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-ink-500">{{ __('ui.rubrics.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rubrics->links() }}</div>

    @include('livewire.partials.confirm-delete-modal')
</div>
