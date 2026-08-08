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
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if(! in_array($type, ['file', 'password'], true)) value="{{ old($name, $value) }}" @endif
        @if($resolvedDir) dir="{{ $resolvedDir }}" @endif
        @if($required) required @endif
        @disabled($disabled)
        {{ $attributes->except(['id'])->class([
            'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-500 disabled:shadow-none disabled:ring-0 disabled:opacity-100 read-only:bg-slate-50 read-only:text-slate-600',
            'text-left' => $resolvedDir === 'ltr',
        ]) }}
    >

    @if($hint)<p class="mt-1.5 text-xs leading-5 text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
</div>
