@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class(['rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center']) }}>
    @if($title)<div class="font-bold text-slate-800">{{ $title }}</div>@endif
    @if($description)<p class="mt-1 text-sm leading-6 text-slate-500">{{ $description }}</p>@endif
    @if(! $slot->isEmpty())<div class="mt-4">{{ $slot }}</div>@endif
</div>
