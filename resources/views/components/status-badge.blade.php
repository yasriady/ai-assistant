@props([
    'status' => null,
])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

    $classes = match ($value) {
        'pending', 'uploaded', 'draft' => 'bg-ink-100 text-ink-700 ring-ink-200',
        'processing', 'queued', 'running' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'assessed', 'completed', 'success' => 'bg-sky-50 text-sky-800 ring-sky-200',
        'reviewed' => 'bg-brand-50 text-brand-800 ring-brand-200',
        'finalized', 'active', 'published' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'failed', 'error' => 'bg-rose-50 text-rose-800 ring-rose-200',
        default => 'bg-ink-100 text-ink-700 ring-ink-200',
    };

    if ($status instanceof \BackedEnum && method_exists($status, 'label')) {
        $label = $status->label();
    } else {
        $key = 'ui.status.'.$value;
        $label = trans()->has($key) ? __($key) : ucfirst(str_replace('_', ' ', $value));
    }
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>
    {{ $label }}
</span>
