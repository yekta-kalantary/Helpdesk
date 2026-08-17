@props([
    'title' => null,
    'description' => null,
])

@php($emptyStateHook = $attributes->get('data-empty-state'))

@if($emptyStateHook)<div data-empty-state="{{ $emptyStateHook }}">@endif
<div {{ $attributes->except('data-empty-state')->class(['rounded-surface border border-dashed border-border bg-page px-5 py-10 text-center']) }}>
    @if($title)<div class="font-bold text-text">{{ $title }}</div>@endif
    @if($description)<p class="mx-auto mt-2 max-w-xl text-body-sm leading-6 text-text-muted">{{ $description }}</p>@endif
    @if(! $slot->isEmpty())<div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>@endif
</div>
@if($emptyStateHook)</div>@endif
