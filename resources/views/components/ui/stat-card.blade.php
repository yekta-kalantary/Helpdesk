@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'accent' => 'neutral',
])

@php
    $accentClasses = match ($accent) {
        'primary' => 'border-teal-200 bg-teal-50/50',
        'danger' => 'border-red-200 bg-red-50/50',
        default => 'border-workspace-border bg-workspace-surface',
    };
@endphp

<x-ui.card {{ $attributes->class($accentClasses) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-sm font-medium text-slate-500">{{ $label }}</div>
            <div class="mt-2 text-3xl font-black tracking-tight {{ $accent === 'danger' ? 'text-red-700' : 'text-slate-950' }}">{{ $value }}</div>
        </div>
        @if($icon)
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $accent === 'danger' ? 'bg-red-100 text-red-700' : 'bg-teal-50 text-workspace-teal' }}" aria-hidden="true">
                <i class="fa-light {{ $icon }} text-lg"></i>
            </span>
        @endif
    </div>
    @if($hint)<div class="mt-2 text-xs leading-5 text-slate-500">{{ $hint }}</div>@endif
</x-ui.card>
