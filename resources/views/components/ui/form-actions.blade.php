@props([
    'mobileSticky' => false,
])

<div {{ $attributes->class([
    'flex flex-col gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:flex-wrap sm:items-center [&>a]:w-full [&>button]:w-full [&>form]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto sm:[&>form]:w-auto [&>form>button]:w-full',
    'sticky bottom-0 z-20 -mx-4 bg-workspace-surface/95 px-4 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-3 shadow-[0_-8px_24px_rgba(55,53,47,0.08)] backdrop-blur sm:static sm:mx-0 sm:bg-transparent sm:px-0 sm:pb-0 sm:pt-5 sm:shadow-none sm:backdrop-blur-none' => $mobileSticky,
]) }}>
    {{ $slot }}
</div>
