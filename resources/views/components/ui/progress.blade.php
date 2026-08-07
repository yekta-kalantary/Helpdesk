@props([
    'value' => 0,
    'showValue' => true,
])

@php($percent = max(0, min(100, (int) $value)))

<div {{ $attributes->class(['min-w-28']) }}>
    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
        <div class="h-full rounded-full bg-slate-950 transition-all" style="width: {{ $percent }}%"></div>
    </div>
    @if($showValue)<div class="mt-1.5 text-xs font-medium text-slate-500">{{ $percent }}%</div>@endif
</div>
