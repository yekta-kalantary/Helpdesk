@props([
    'tabs' => [],
])

@php($wrapperAttributes = $attributes->class(['min-w-0 overflow-x-auto overscroll-x-contain']))

<nav aria-label="Section navigation" {{ $wrapperAttributes }}>
    <div class="flex min-w-max gap-1 border-b border-workspace-border">
        @foreach($tabs as $tab)
            <a href="{{ $tab['href'] }}" @if($tab['navigate'] ?? false) wire:navigate @endif @if($tab['active']) aria-current="page" @endif @class([
                'ui-loading-stable inline-flex min-h-11 items-center whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-workspace-teal focus-visible:ring-offset-2',
                'border-workspace-teal text-workspace-teal' => $tab['active'],
                'border-transparent text-slate-500 hover:border-teal-200 hover:text-workspace-teal' => ! $tab['active'],
            ])>{{ $tab['label'] }}</a>
        @endforeach
    </div>
</nav>
