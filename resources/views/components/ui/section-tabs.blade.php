@props([
    'tabs' => [],
])

@php($wrapperAttributes = $attributes->class(['shell-section-tabs min-w-0 overflow-x-auto overscroll-x-contain']))

<nav aria-label="ناوبری بخش‌ها" data-section-tabs {{ $wrapperAttributes }}>
    <div class="flex min-w-max border-b border-border">
        @foreach($tabs as $tab)
            <a href="{{ $tab['href'] }}" data-section-tab @if($tab['navigate'] ?? false) wire:navigate @endif @if($tab['active']) aria-current="location" @endif @class([
                'ui-loading-stable inline-flex min-h-11 items-center whitespace-nowrap border-b-2 px-3 py-2 text-body-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2',
                'border-accent text-text' => $tab['active'],
                'border-transparent text-text-muted hover:border-accent/40 hover:text-text' => ! $tab['active'],
            ])>{{ $tab['label'] }}</a>
        @endforeach
    </div>
</nav>
