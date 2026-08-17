@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->class(['shell-breadcrumbs min-w-0']) }}>
    <ol class="flex min-w-0 flex-wrap items-center gap-2 text-body-sm text-text-muted">
        @foreach($items as $item)
            @php($isCurrent = $loop->last)
            <li class="flex min-w-0 items-center gap-2">
                @if($loop->first === false)
                    <i class="fa-light fa-chevron-left text-caption text-text-muted" aria-hidden="true"></i>
                @endif
                @if($isCurrent || ! $item['href'])
                    <span class="truncate font-semibold {{ $isCurrent ? 'text-text' : '' }}" @if($isCurrent) aria-current="page" @endif>{{ $item['label'] }}</span>
                @else
                    <a class="ui-loading-stable inline-flex min-h-11 items-center truncate rounded-control py-2 font-medium transition-colors duration-150 hover:text-primary focus-visible:outline focus-visible:outline-3 focus-visible:outline-focus focus-visible:outline-offset-2" href="{{ $item['href'] }}" wire:navigate>{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
