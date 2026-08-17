@props([
    'label',
    'valueClass' => 'text-text',
])

<div {{ $attributes->class(['min-w-0']) }}>
    <dt class="text-caption font-medium text-text-muted">{{ $label }}</dt>
    <dd class="mt-1 break-words text-body-sm font-semibold {{ $valueClass }}">{{ $slot }}</dd>
</div>
