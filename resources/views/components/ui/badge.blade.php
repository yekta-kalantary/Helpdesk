@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-workspace-success-surface text-workspace-success',
        'danger' => 'bg-workspace-danger-surface text-workspace-danger',
        'warning' => 'bg-workspace-warning-surface text-workspace-warning',
        'info' => 'bg-workspace-info-surface text-workspace-info',
        default => 'bg-workspace-neutral-surface text-workspace-text',
    };
@endphp

<span {{ $attributes->class(["inline-flex min-h-7 items-center rounded-workspace px-2 py-1 text-xs font-semibold leading-5 {$classes}"]) }}>{{ $slot }}</span>
