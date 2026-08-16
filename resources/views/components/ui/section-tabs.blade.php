@props([
    'tabs' => [],
])

<nav aria-label="Section navigation" class="min-w-0 overflow-x-auto overscroll-x-contain" {{ $attributes }}>
    <div class="flex min-w-max gap-1 border-b border-workspace-border">
        @foreach($tabs as $tab)
            <a href="{{ $tab['href'] }}" wire:navigate @if($tab['active']) aria-current="page" @endif {{ $attributes->except(['class'])->class([
                'ui-loading-stable min-h-11 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition',
                'border-workspace-teal text-workspace-teal' => $tab['active'],
                'border-transparent text-slate-500 hover:border-teal-200 hover:text-workspace-teal' => ! $tab['active'],
            ]) }}>{{ $tab['label'] }}</a>
        @endforeach
    </div>
</nav>
