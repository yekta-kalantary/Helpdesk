@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'dir' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name));
    $ltrTypes = ['email', 'password', 'tel', 'url', 'number', 'date', 'datetime-local', 'month', 'time', 'week'];
    $ltrInputModes = ['email', 'numeric', 'decimal', 'tel', 'url'];
    $inputMode = strtolower((string) $attributes->get('inputmode', ''));
    $hasLtrSemanticName = preg_match(
        '/(?:^|[_\-.])(email|mobile|phone|tel|telephone|password|username|url|website|domain|slug|code|postal_code|zip|national_id|iban|card|account|ip|mac|uuid|token|api_key|secret|hash)(?:$|[_\-.])/i',
        (string) $name,
    ) === 1;
    $errorId = $id.'-error';
    $resolvedDir = $dir ?? (
        in_array($type, $ltrTypes, true)
        || in_array($inputMode, $ltrInputModes, true)
        || $hasLtrSemanticName
            ? 'ltr'
            : null
    );
@endphp

<div>
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-label font-semibold text-field-label">{{ $label }}</label>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if(! in_array($type, ['file', 'password'], true)) value="{{ old($name, $value) }}" @endif
        @if($resolvedDir) dir="{{ $resolvedDir }}" @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $errorId }}" @enderror
        @if($required) required @endif
        @disabled($disabled)
        {{ $attributes->except(['id'])->class([
            'min-h-11 w-full rounded-control border border-input-border bg-input-background px-3 py-2 text-body-sm text-text outline-none transition placeholder:text-text-muted focus:border-focus focus:ring-2 focus:ring-focus/20 disabled:cursor-not-allowed disabled:border-border disabled:bg-surface-muted disabled:text-text-muted disabled:shadow-none disabled:ring-0 disabled:opacity-100 read-only:bg-surface-muted read-only:text-text-muted',
            'text-left' => $resolvedDir === 'ltr',
        ]) }}
    >

    @if($hint)<p class="mt-1.5 text-caption leading-5 text-field-helper">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $errorId }}" class="mt-1.5 text-caption font-medium text-field-error">{{ $message }}</p>@enderror
</div>
