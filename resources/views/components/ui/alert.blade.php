@props([
    'tone' => 'info',
])

@php
    $classes = match ($tone) {
        'success' => 'border-emerald-200/80 bg-emerald-50 text-workspace-success',
        'danger', 'error' => 'border-red-200 bg-red-50 text-workspace-danger',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        default => 'border-workspace-divider bg-workspace-page text-workspace-text',
    };
@endphp

<div role="{{ in_array($tone, ['danger', 'error', 'warning'], true) ? 'alert' : 'status' }}" {{ $attributes->class(["rounded-lg border px-4 py-3 text-sm leading-6 {$classes}"]) }}>
    {{ $slot }}
</div>
