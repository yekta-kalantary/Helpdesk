@props([
    'href',
    'active' => false,
    'icon' => null,
])

<a href="{{ $href }}" wire:navigate {{ $attributes->class([
    'flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition',
    'bg-slate-950 text-white shadow-sm' => $active,
    'text-slate-600 hover:bg-slate-100 hover:text-slate-950' => ! $active,
]) }}>
    <span class="flex min-w-0 items-center gap-3">
        @if($icon)
            <i class="fa-light {{ $icon }} fa-fw shrink-0 text-base" aria-hidden="true"></i>
        @endif
        <span class="truncate">{{ $slot }}</span>
    </span>
    @isset($meta){{ $meta }}@endisset
</a>
