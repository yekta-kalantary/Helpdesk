@props([
    'tone' => 'info',
    'title',
    'message' => null,
])

@php
    $classes = match ($tone) {
        'success' => 'border-emerald-200 bg-emerald-50 text-workspace-success',
        'danger', 'error' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        default => 'border-teal-200 bg-teal-50 text-workspace-teal',
    };
@endphp

<div x-data="{ open: true }" x-show="open" x-cloak role="status" aria-live="polite" {{ $attributes->class(["relative rounded-xl border px-4 py-3 pe-12 text-sm leading-6 {$classes}"]) }}>
    <div class="font-bold">{{ $title }}</div>
    @if($message)<p class="mt-1">{{ $message }}</p>@endif
    <button type="button" class="absolute end-2 top-2 inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg hover:bg-black/5" aria-label="بستن اعلان" x-on:click="open = false">
        <i class="fa-light fa-xmark" aria-hidden="true"></i>
    </button>
</div>
