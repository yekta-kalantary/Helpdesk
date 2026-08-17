@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'accent' => 'neutral',
])

@php
    $accentClasses = match ($accent) {
        'primary' => 'border-info bg-page',
        'danger' => 'border-danger bg-page',
        default => 'border-border bg-surface',
    };
@endphp

<x-ui.card {{ $attributes->class($accentClasses) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-body-sm font-medium text-text-muted">{{ $label }}</div>
            <div class="mt-1 text-heading-xl font-semibold tracking-tight {{ $accent === 'danger' ? 'text-danger-text' : 'text-text' }}">{{ $value }}</div>
        </div>
        @if($icon)
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-control {{ $accent === 'danger' ? 'bg-danger-surface text-danger-text' : 'bg-page text-info-text' }}" aria-hidden="true">
                <i class="fa-light {{ $icon }} text-lg"></i>
            </span>
        @endif
    </div>
    @if($hint)<div class="mt-1 text-caption leading-5 text-text-muted">{{ $hint }}</div>@endif
</x-ui.card>
