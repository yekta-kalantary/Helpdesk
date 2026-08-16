@props([
    'name',
    'value',
    'active' => false,
    'ariaLabel' => null,
])

<button type="button" name="{{ $name }}" value="{{ $value }}" aria-label="{{ $ariaLabel ?? $value }}" aria-pressed="{{ $active ? 'true' : 'false' }}" {{ $attributes->class([
    'ui-loading-stable inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg px-3 text-sm font-semibold transition',
    'bg-workspace-teal text-white' => $active,
    'text-slate-500 hover:bg-teal-50 hover:text-workspace-teal' => ! $active,
]) }}>{{ $slot }}</button>
