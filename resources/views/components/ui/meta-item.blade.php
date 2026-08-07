@props(['label'])

<div {{ $attributes }}>
    <div class="text-xs font-medium text-slate-500">{{ $label }}</div>
    <div class="mt-1 text-sm font-semibold text-slate-800">{{ $slot }}</div>
</div>
