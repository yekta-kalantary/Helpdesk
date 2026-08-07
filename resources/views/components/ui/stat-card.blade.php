@props([
    'label',
    'value',
    'hint' => null,
])

<x-ui.card {{ $attributes }}>
    <div class="text-sm font-medium text-slate-500">{{ $label }}</div>
    <div class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ $value }}</div>
    @if($hint)<div class="mt-2 text-xs leading-5 text-slate-500">{{ $hint }}</div>@endif
</x-ui.card>
