@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'accent' => 'neutral',
])

@php
    $accentClasses = match ($accent) {
        'primary' => 'border-workspace-teal/30 bg-workspace-page',
        'danger' => 'border-workspace-danger/30 bg-workspace-page',
        default => 'border-workspace-divider/80 bg-workspace-surface',
    };
@endphp

<x-ui.card {{ $attributes->class($accentClasses) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-sm font-medium text-workspace-muted">{{ $label }}</div>
            <div class="mt-1 text-2xl font-black tracking-tight {{ $accent === 'danger' ? 'text-workspace-danger' : 'text-workspace-text' }}">{{ $value }}</div>
        </div>
        @if($icon)
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $accent === 'danger' ? 'bg-red-50 text-workspace-danger' : 'bg-workspace-page text-workspace-teal' }}" aria-hidden="true">
                <i class="fa-light {{ $icon }} text-lg"></i>
            </span>
        @endif
    </div>
    @if($hint)<div class="mt-1 text-xs leading-5 text-workspace-muted">{{ $hint }}</div>@endif
</x-ui.card>
