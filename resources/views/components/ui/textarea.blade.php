@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'required' => false,
])

@php($id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name)))

<div>
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->except(['id'])->class(['min-h-28 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm leading-6 text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-200']) }}
    >{{ old($name, $value) }}</textarea>

    @if($hint)<p class="mt-1.5 text-xs leading-5 text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
</div>
