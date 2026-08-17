@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class(['rounded-workspace border border-dashed border-workspace-divider bg-workspace-page px-5 py-10 text-center']) }}>
    @if($title)<div class="font-bold text-workspace-text">{{ $title }}</div>@endif
    @if($description)<p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-workspace-muted">{{ $description }}</p>@endif
    @if(! $slot->isEmpty())<div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>@endif
</div>
