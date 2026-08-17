@php
    $current = app()->getLocale();
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md border border-ink-200 bg-white p-0.5 text-xs font-semibold']) }} role="group" aria-label="{{ __('ui.language.label') }}">
    <a
        href="{{ route('locale.switch', 'id') }}"
        @class([
            'rounded px-2 py-1 transition',
            'bg-ink-900 text-white' => $current === 'id',
            'text-ink-500 hover:text-ink-800' => $current !== 'id',
        ])
    >{{ __('ui.language.id') }}</a>
    <a
        href="{{ route('locale.switch', 'en') }}"
        @class([
            'rounded px-2 py-1 transition',
            'bg-ink-900 text-white' => $current === 'en',
            'text-ink-500 hover:text-ink-800' => $current !== 'en',
        ])
    >{{ __('ui.language.en') }}</a>
</div>
