@php
    $activeTerm = app(\App\Services\Term\ActiveTerm::class);
    $active = $activeTerm->current();
    $options = $activeTerm->selectableOptions();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <label for="active-term-select" class="hidden text-xs font-medium uppercase tracking-wide text-ink-500 sm:inline">
        {{ __('ui.term.code') }}
    </label>
    <select
        id="active-term-select"
        name="active_term"
        class="min-w-[14rem] max-w-[20rem] cursor-pointer rounded-md border border-ink-300 bg-white py-1.5 pl-3 pr-8 text-sm font-medium text-ink-900 shadow-sm outline-none transition hover:border-brand-600 focus:border-brand-600 focus:ring-2 focus:ring-brand-100"
        onchange="if (this.value) { window.location.href = this.value; }"
    >
        @foreach ($options as $option)
            <option
                value="{{ route('term.switch', ['term' => $option['code']]) }}"
                @selected($option['code'] === $active)
            >
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
</div>
