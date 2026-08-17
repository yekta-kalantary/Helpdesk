@props([
    'label',
    'valueClass' => 'text-slate-800',
])

<div {{ $attributes->class(['min-w-0']) }}>
    <div class="text-xs font-medium text-slate-500">{{ $label }}</div>
    <div class="mt-1 break-words text-sm font-semibold {{ $valueClass }}">{{ $slot }}</div>
</div>
