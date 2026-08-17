@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<section {{ $attributes->class([
    'min-w-0 rounded-surface border border-border bg-surface',
    'p-4 sm:p-6' => $padding,
]) }}>
    @if($title || $subtitle || isset($actions))
        <header class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                @if($title)<h2 class="break-words font-bold text-text">{{ $title }}</h2>@endif
                @if($subtitle)<p class="mt-1 break-words text-body-sm leading-6 text-text-muted">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center [&>a]:w-full [&>button]:w-full [&>form]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto sm:[&>form]:w-auto [&>form>button]:w-full">
                    {{ $actions }}
                </div>
            @endisset
        </header>
    @endif

    {{ $slot }}
</section>
