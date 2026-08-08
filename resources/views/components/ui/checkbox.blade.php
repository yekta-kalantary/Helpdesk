@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'hint' => null,
    'model' => null,
    'live' => false,
    'disabled' => false,
])

@php($id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name)))

<label
    for="{{ $id }}"
    @if($disabled) aria-disabled="true" @endif
    {{ $attributes->except(['id'])->class([
        'flex items-start gap-3 rounded-xl border p-3.5 transition',
        'cursor-pointer border-slate-200 bg-white hover:border-slate-300' => ! $disabled,
        'cursor-not-allowed border-slate-200 bg-slate-100 opacity-70' => $disabled,
    ]) }}
>
    <input
        id="{{ $id }}"
        class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-slate-950 focus:ring-slate-400 disabled:cursor-not-allowed disabled:opacity-50"
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @disabled($disabled)
        @if($model && $live) wire:model.live="{{ $model }}" @elseif($model) wire:model="{{ $model }}" @else @checked($checked) @endif
    >
    <span class="min-w-0">
        <span class="block text-sm font-semibold {{ $disabled ? 'text-slate-500' : 'text-slate-800' }}">{{ $label }}</span>
        @if($hint)<span class="mt-1 block text-xs leading-5 text-slate-500">{{ $hint }}</span>@endif
    </span>
</label>
