@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-badge-success-background text-badge-success-text',
        'danger' => 'bg-badge-danger-background text-badge-danger-text',
        'warning' => 'bg-badge-warning-background text-badge-warning-text',
        'info' => 'bg-info-surface text-info-text',
        default => 'bg-badge-neutral-background text-badge-neutral-text',
    };
@endphp

<span data-tone="{{ $tone }}" {{ $attributes->class(["inline-flex min-h-7 items-center gap-1 rounded-control px-2 py-1 text-caption font-semibold leading-5 {$classes}"]) }}>{{ $slot }}</span>
