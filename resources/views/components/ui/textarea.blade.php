@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'dir' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name));
    $hintId = $id.'-hint';
    $errorId = $id.'-error';
    $hasError = $errors->has($name);
    $hasHint = filled($hint);
    $describedBy = collect([$hasHint ? $hintId : null, $hasError ? $errorId : null])->filter()->implode(' ');
@endphp

<div>
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-label font-semibold text-field-label">{{ $label }}</label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        @if($dir) dir="{{ $dir }}" @endif
        @if($hasError) aria-invalid="true" @endif
        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if($required) required @endif
        @disabled($disabled)
        {{ $attributes->except(['id'])->class([
            'min-h-28 w-full rounded-control border border-input-border bg-input-background px-3 py-2 text-body-sm leading-6 text-text outline-none transition placeholder:text-text-muted focus:border-focus focus:ring-2 focus:ring-focus/20 disabled:cursor-not-allowed disabled:border-border disabled:bg-surface-muted disabled:text-text-muted disabled:shadow-none disabled:ring-0 disabled:opacity-100 read-only:bg-surface-muted read-only:text-text-muted',
            'border-input-border-error' => $hasError,
            'text-left' => $dir === 'ltr',
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @if($hint)<p id="{{ $hintId }}" class="mt-1.5 text-caption leading-5 text-field-helper">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $errorId }}" class="mt-1.5 text-caption font-medium text-field-error">{{ $message }}</p>@enderror
</div>
