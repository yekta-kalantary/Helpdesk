@props([
    'value' => 0,
    'showValue' => true,
])

@php($percent = max(0, min(100, (int) $value)))

<div {{ $attributes->class(['min-w-28']) }}>
    <div class="h-2 overflow-hidden rounded-control bg-surface-muted" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" aria-label="پیشرفت">
        <div class="ui-progress-bar h-full rounded-control bg-primary transition-all" data-progress="{{ $percent }}"></div>
    </div>
    @if($showValue)<div class="mt-1.5 text-caption font-medium text-text-muted">{{ $percent }}%</div>@endif
</div>
