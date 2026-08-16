@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-50 text-workspace-success ring-emerald-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'info' => 'bg-teal-50 text-workspace-teal ring-teal-600/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-500/10',
    };
@endphp

<span {{ $attributes->class(["inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$classes}"]) }}>{{ $slot }}</span>
