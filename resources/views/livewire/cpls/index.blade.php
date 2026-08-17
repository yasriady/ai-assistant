<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.cpl.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('ui.cpl.subtitle') }}</p>
        </div>
        <a href="{{ route('cpls.import') }}" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white hover:bg-brand-800">{{ __('ui.cpl.import') }}</a>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('ui.cpl.search') }}" class="w-full max-w-md rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
        <select wire:model.live="category" class="rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
            <option value="">{{ __('ui.cpl.all_categories') }}</option>
            @foreach ($categories as $item)
                <option value="{{ $item->value }}">{{ $item->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.cpl.code') }}</th>
                    <th class="px-4 py-3">{{ __('ui.cpl.official_code') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.type') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.description') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($outcomes as $outcome)
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $outcome->code }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $outcome->official_code }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $outcome->category->label() }}</td>
                        <td class="px-4 py-3 text-ink-700">{{ $outcome->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-ink-500">{{ __('ui.cpl.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $outcomes->links() }}</div>
</div>
