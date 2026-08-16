@props([
    'tone' => 'info',
])

@php
    $classes = match ($tone) {
        'success' => 'border-emerald-200 bg-emerald-50 text-workspace-success',
        'danger', 'error' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        default => 'border-teal-200 bg-teal-50 text-workspace-teal',
    };
@endphp

<div role="alert" {{ $attributes->class(["rounded-xl border px-4 py-3 text-sm leading-6 {$classes}"]) }}>
    {{ $slot }}
</div>
