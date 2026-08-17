@props([
    'tone' => 'info',
])

@php
    $classes = match ($tone) {
        'success' => 'border-success bg-success-surface text-success-text',
        'danger', 'error' => 'border-danger bg-danger-surface text-danger-text',
        'warning' => 'border-warning bg-warning-surface text-warning-text',
        'neutral' => 'border-border bg-surface-muted text-text',
        default => 'border-border bg-info-surface text-info-text',
    };
@endphp

<div role="{{ in_array($tone, ['danger', 'error', 'warning'], true) ? 'alert' : 'status' }}" {{ $attributes->class(["rounded-surface border px-4 py-3 text-body-sm leading-6 {$classes}"]) }}>
    {{ $slot }}
</div>
