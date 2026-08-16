@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
])

<x-ui.card {{ $attributes }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-sm font-medium text-slate-500">{{ $label }}</div>
            <div class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ $value }}</div>
        </div>
        @if($icon)
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-workspace-teal" aria-hidden="true">
                <i class="fa-light {{ $icon }} text-lg"></i>
            </span>
        @endif
    </div>
    @if($hint)<div class="mt-2 text-xs leading-5 text-slate-500">{{ $hint }}</div>@endif
</x-ui.card>
