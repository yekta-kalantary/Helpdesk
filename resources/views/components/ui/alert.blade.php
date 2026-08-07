@props([
    'tone' => 'info',
])

@php
    $classes = match ($tone) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'danger', 'error' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        default => 'border-sky-200 bg-sky-50 text-sky-800',
    };
@endphp

<div role="alert" {{ $attributes->class(["rounded-xl border px-4 py-3 text-sm leading-6 {$classes}"]) }}>
    {{ $slot }}
</div>
