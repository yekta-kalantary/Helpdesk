@props([
    'href',
    'active' => false,
    'icon' => null,
])

<a href="{{ $href }}" wire:navigate @if($active) aria-current="page" @endif {{ $attributes->class([
    'ui-loading-stable flex min-h-11 items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition',
    'bg-workspace-neutral-surface text-workspace-text' => $active,
    'text-workspace-muted hover:bg-workspace-neutral-surface hover:text-workspace-text' => ! $active,
]) }}>
    <span class="flex min-w-0 items-center gap-3">
        @if($icon)
            <i class="fa-light {{ $icon }} fa-fw shrink-0 text-base" aria-hidden="true"></i>
        @endif
        <span class="truncate">{{ $slot }}</span>
    </span>
    @isset($meta){{ $meta }}@endisset
</a>
