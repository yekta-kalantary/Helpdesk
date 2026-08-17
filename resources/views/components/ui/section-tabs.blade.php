@props([
    'tabs' => [],
])

@php($wrapperAttributes = $attributes->class(['min-w-0 overflow-x-auto overscroll-x-contain']))

<nav aria-label="Section navigation" {{ $wrapperAttributes }}>
    <div class="flex min-w-max gap-2 border-b border-workspace-divider">
        @foreach($tabs as $tab)
            <a href="{{ $tab['href'] }}" @if($tab['navigate'] ?? false) wire:navigate @endif @if($tab['active']) aria-current="location" @endif @class([
                'ui-loading-stable inline-flex min-h-11 items-center whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-workspace-focus focus-visible:ring-offset-2',
                'border-workspace-accent text-workspace-text' => $tab['active'],
                'border-transparent text-workspace-muted hover:border-workspace-accent/40 hover:text-workspace-text' => ! $tab['active'],
            ])>{{ $tab['label'] }}</a>
        @endforeach
    </div>
</nav>
