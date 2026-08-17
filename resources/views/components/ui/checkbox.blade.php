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
        'flex items-start gap-3 rounded-surface border p-3.5 transition',
        'cursor-pointer border-border bg-surface hover:border-primary' => ! $disabled,
        'cursor-not-allowed border-border bg-surface-muted opacity-70' => $disabled,
    ]) }}
>
    <input
        id="{{ $id }}"
        class="mt-0.5 h-4 w-4 shrink-0 rounded-control border-input-border text-primary focus:ring-focus disabled:cursor-not-allowed disabled:opacity-50"
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @disabled($disabled)
        @if($model && $live) wire:model.live="{{ $model }}" @elseif($model) wire:model="{{ $model }}" @else @checked($checked) @endif
    >
    <span class="min-w-0">
        <span class="block text-body-sm font-semibold {{ $disabled ? 'text-text-muted' : 'text-text' }}">{{ $label }}</span>
        @if($hint)<span class="mt-1 block text-caption leading-5 text-text-muted">{{ $hint }}</span>@endif
    </span>
</label>
