@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->class(['min-w-0']) }}>
    <ol class="flex min-w-0 flex-wrap items-center gap-2 text-sm text-slate-500">
        @foreach($items as $item)
            @php($isCurrent = $loop->last)
            <li class="flex min-w-0 items-center gap-2">
                @if($loop->first === false)
                    <i class="fa-light fa-chevron-left text-xs text-slate-400" aria-hidden="true"></i>
                @endif
                @if($isCurrent || ! $item['href'])
                    <span class="truncate font-semibold {{ $isCurrent ? 'text-slate-900' : '' }}" @if($isCurrent) aria-current="page" @endif>{{ $item['label'] }}</span>
                @else
                    <a class="ui-loading-stable inline-flex min-h-11 items-center truncate rounded-md py-2 font-medium transition-colors duration-150 hover:text-workspace-teal focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2" href="{{ $item['href'] }}" wire:navigate>{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
