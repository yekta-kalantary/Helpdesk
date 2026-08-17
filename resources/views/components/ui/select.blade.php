@props([
    'name',
    'label' => null,
    'hint' => null,
    'dir' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name));
    $errorId = $id.'-error';
@endphp

<div>
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-label font-semibold text-field-label">{{ $label }}</label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if($dir) dir="{{ $dir }}" @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $errorId }}" @enderror
        @if($required) required @endif
        @disabled($disabled)
        {{ $attributes->except(['id'])->class([
            'min-h-11 w-full rounded-control border border-input-border bg-input-background px-3 py-2 text-body-sm text-text outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20 disabled:cursor-not-allowed disabled:border-border disabled:bg-surface-muted disabled:text-text-muted disabled:shadow-none disabled:ring-0 disabled:opacity-100',
            'text-left' => $dir === 'ltr',
        ]) }}
    >
        {{ $slot }}
    </select>

    @if($hint)<p class="mt-1.5 text-caption leading-5 text-field-helper">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $errorId }}" class="mt-1.5 text-caption font-medium text-field-error">{{ $message }}</p>@enderror
</div>
