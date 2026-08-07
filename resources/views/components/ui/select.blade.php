@props([
    'name',
    'label' => null,
    'hint' => null,
    'dir' => null,
    'required' => false,
])

@php($id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name)))

<div>
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if($dir) dir="{{ $dir }}" @endif
        @if($required) required @endif
        {{ $attributes->except(['id'])->class(['w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200']) }}
    >
        {{ $slot }}
    </select>

    @if($hint)<p class="mt-1.5 text-xs leading-5 text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
</div>
