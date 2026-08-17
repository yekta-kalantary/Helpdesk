@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-50 text-workspace-success',
        'danger' => 'bg-red-50 text-workspace-danger',
        'warning' => 'bg-amber-50 text-amber-800',
        'info' => 'bg-teal-50 text-workspace-teal',
        default => 'bg-slate-100 text-workspace-text',
    };
@endphp

<span {{ $attributes->class(["inline-flex min-h-7 items-center rounded-md px-2 py-1 text-xs font-semibold leading-5 {$classes}"]) }}>{{ $slot }}</span>
