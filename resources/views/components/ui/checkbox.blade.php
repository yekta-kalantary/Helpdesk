@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'hint' => null,
    'model' => null,
])

@php($id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name)))

<label for="{{ $id }}" {{ $attributes->except(['id'])->class(['flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition hover:border-slate-300']) }}>
    <input
        id="{{ $id }}"
        class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-slate-950 focus:ring-slate-400"
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @if($model) wire:model="{{ $model }}" @else @checked($checked) @endif
    >
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
        @if($hint)<span class="mt-1 block text-xs leading-5 text-slate-500">{{ $hint }}</span>@endif
    </span>
</label>
