@props([
    'label',
    'valueClass' => 'text-slate-800',
])

<div {{ $attributes->class(['min-w-0']) }}>
    <dt class="text-xs font-medium text-workspace-muted">{{ $label }}</dt>
    <dd class="mt-1 break-words text-sm font-semibold {{ $valueClass }}">{{ $slot }}</dd>
</div>
