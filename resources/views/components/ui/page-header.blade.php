@props([
    'title',
    'subtitle' => null,
])

<header {{ $attributes->class(['mb-6 flex flex-wrap items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-black tracking-tight text-slate-950">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
