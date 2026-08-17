@props([
    'tone' => 'info',
    'title',
    'message' => null,
])

@php
    $classes = match ($tone) {
        'success' => 'border-success bg-success-surface text-success-text',
        'danger', 'error' => 'border-danger bg-danger-surface text-danger-text',
        'warning' => 'border-warning bg-warning-surface text-warning-text',
        default => 'border-info bg-info-surface text-info-text',
    };
@endphp

<div x-data="{ open: true }" x-show="open" x-cloak role="status" aria-live="polite" {{ $attributes->class(["relative rounded-surface border px-4 py-3 pe-12 text-body-sm leading-6 {$classes}"]) }}>
    <div class="font-bold">{{ $title }}</div>
    @if($message)<p class="mt-1">{{ $message }}</p>@endif
    <button type="button" class="absolute end-2 top-2 inline-flex min-h-11 min-w-11 items-center justify-center rounded-control hover:bg-surface-muted focus-visible:ring-2 focus-visible:ring-focus" aria-label="بستن اعلان" x-on:click="open = false">
        <i class="fa-light fa-xmark" aria-hidden="true"></i>
    </button>
</div>
