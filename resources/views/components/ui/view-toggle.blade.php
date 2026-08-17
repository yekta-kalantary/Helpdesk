@props([
    'name',
    'value',
    'active' => false,
    'ariaLabel' => null,
])

<button type="button" name="{{ $name }}" value="{{ $value }}" aria-label="{{ $ariaLabel ?? $value }}" aria-pressed="{{ $active ? 'true' : 'false' }}" {{ $attributes->class([
    'ui-loading-stable inline-flex min-h-11 min-w-11 items-center justify-center rounded-control px-3 text-body-sm font-semibold transition',
    'bg-primary text-surface' => $active,
    'text-text-muted hover:bg-primary-surface hover:text-primary' => ! $active,
]) }}>{{ $slot }}</button>
