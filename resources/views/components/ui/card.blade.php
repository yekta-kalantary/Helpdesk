@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<section {{ $attributes->class([
    'rounded-2xl border border-slate-200 bg-white shadow-sm',
    'p-5 sm:p-6' => $padding,
]) }}>
    @if($title || $subtitle || isset($actions))
        <header class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                @if($title)<h2 class="font-bold text-slate-950">{{ $title }}</h2>@endif
                @if($subtitle)<p class="mt-1 text-sm leading-6 text-slate-500">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif

    {{ $slot }}
</section>
