@props([
    'tone' => 'info',
])

@php
    $classes = match ($tone) {
        'success' => 'border-workspace-success/30 bg-workspace-success-surface text-workspace-success',
        'danger', 'error' => 'border-workspace-danger/30 bg-workspace-danger-surface text-workspace-danger',
        'warning' => 'border-workspace-warning/30 bg-workspace-warning-surface text-workspace-warning',
        'neutral' => 'border-workspace-divider bg-workspace-neutral-surface text-workspace-neutral',
        default => 'border-workspace-divider bg-workspace-info-surface text-workspace-info',
    };
@endphp

<div role="{{ in_array($tone, ['danger', 'error', 'warning'], true) ? 'alert' : 'status' }}" {{ $attributes->class(["rounded-workspace border px-4 py-3 text-sm leading-6 {$classes}"]) }}>
    {{ $slot }}
</div>
