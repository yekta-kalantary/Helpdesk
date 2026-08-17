@props(['action' => null, 'livewire' => false, 'mobileLabel' => 'فیلترها', 'activeCount' => 0])

@php
    $filterSummary = $activeCount > 0
        ? "{$activeCount} فیلتر فعال"
        : 'فیلترهای تکمیلی';
@endphp

@if($livewire)
    <div {{ $attributes->class(['mb-5 w-full sm:mb-6']) }}>
        <details class="mobile-filter-details group">
            <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-xl border border-workspace-border bg-workspace-surface px-4 text-sm font-bold text-slate-700 marker:hidden hover:border-workspace-teal focus:outline-none focus-visible:ring-2 focus-visible:ring-workspace-focus">
                <span class="flex items-center gap-2">
                    <span>{{ $mobileLabel }}</span>
                    <span class="rounded-full bg-workspace-neutral-surface px-2 py-0.5 text-xs font-semibold text-slate-500" data-active-filter-count>{{ $filterSummary }}</span>
                </span>
                <i class="fa-light fa-chevron-down text-xs transition-transform group-open:rotate-180" aria-hidden="true"></i>
            </summary>
            <div class="mt-3 flex w-full flex-col gap-3 sm:mt-0 sm:flex-row sm:items-end [&>*]:min-w-0 [&>a]:w-full [&>button]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto">
                {{ $slot }}
            </div>
        </details>
    </div>
@else
    <form method="GET" action="{{ $action ?: url()->current() }}" {{ $attributes->class(['mb-5 w-full sm:mb-6']) }}>
        <details class="mobile-filter-details group">
            <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-xl border border-workspace-border bg-workspace-surface px-4 text-sm font-bold text-slate-700 marker:hidden hover:border-workspace-teal focus:outline-none focus-visible:ring-2 focus-visible:ring-workspace-focus">
                <span class="flex items-center gap-2">
                    <span>{{ $mobileLabel }}</span>
                    <span class="rounded-full bg-workspace-neutral-surface px-2 py-0.5 text-xs font-semibold text-slate-500" data-active-filter-count>{{ $filterSummary }}</span>
                </span>
                <i class="fa-light fa-chevron-down text-xs transition-transform group-open:rotate-180" aria-hidden="true"></i>
            </summary>
            <div class="mt-3 flex w-full flex-col gap-3 sm:mt-0 sm:flex-row sm:items-end [&>*]:min-w-0 [&>a]:w-full [&>button]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto">
                {{ $slot }}
            </div>
        </details>
    </form>
@endif
