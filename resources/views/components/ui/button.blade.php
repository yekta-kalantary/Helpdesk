@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-300',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus:ring-red-300',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus:ring-slate-300',
        default => 'bg-slate-950 text-white hover:bg-slate-800 focus:ring-slate-400',
    };

    $sizeClasses = match ($size) {
        'sm' => 'min-h-8 px-3 py-1.5 text-xs',
        'lg' => 'min-h-11 px-5 py-2.5 text-sm',
        default => 'min-h-9 px-4 py-2 text-sm',
    };

    $classes = "inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 {$variantClasses} {$sizeClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</button>
@endif
