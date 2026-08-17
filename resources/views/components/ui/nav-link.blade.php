@props([
    'href',
    'active' => false,
    'icon' => null,
])

<a href="{{ $href }}" wire:navigate @if($active) aria-current="page" @endif {{ $attributes->class([
    'ui-loading-stable shell-nav-link flex min-h-11 items-center justify-between gap-3 rounded-control px-3 py-2.5 text-body-sm font-semibold transition',
    'bg-primary-surface text-primary-text' => $active,
    'text-text-muted hover:bg-surface-muted hover:text-text' => ! $active,
]) }}>
    <span class="flex min-w-0 items-center gap-3">
        @if($icon)
            <i class="fa-light {{ $icon }} fa-fw shrink-0 text-base" aria-hidden="true"></i>
        @endif
        <span class="truncate">{{ $slot }}</span>
    </span>
    @isset($meta){{ $meta }}@endisset
</a>
