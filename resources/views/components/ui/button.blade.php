@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
    'icon' => null,
    'loading' => false,
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'border border-border bg-surface text-text hover:border-primary hover:bg-primary-surface focus:ring-focus',
        'danger' => 'border border-danger bg-danger text-surface hover:bg-danger-text focus:ring-focus',
        'ghost' => 'bg-transparent text-text-muted hover:bg-primary-surface hover:text-primary focus:ring-focus',
        default => 'bg-primary text-surface hover:bg-primary-text focus:ring-focus',
    };

    $sizeClasses = match ($size) {
        'sm' => 'min-h-11 px-3 py-1.5 text-xs',
        'lg' => 'min-h-11 px-5 py-2.5 text-sm',
        default => 'min-h-11 px-4 py-2 text-sm',
    };

    $iconClasses = match ($size) {
        'sm' => 'text-xs',
        'lg' => 'text-base',
        default => 'text-sm',
    };

    $classes = "ui-loading-stable inline-flex min-w-11 items-center justify-center gap-2 rounded-button font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 {$variantClasses} {$sizeClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" @if($loading) aria-busy="true" @endif {{ $attributes->class([$classes]) }}>
        @if($icon)<i class="fa-light {{ $icon }} fa-fw {{ $iconClasses }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @if($loading) aria-busy="true" @endif {{ $attributes->class([$classes]) }}>
        @if($icon)<i class="fa-light {{ $icon }} fa-fw {{ $iconClasses }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </button>
@endif
