@props([
    'mobileSticky' => false,
])

<div {{ $attributes->class([
    'flex flex-col gap-2 border-t border-workspace-divider pt-5 sm:flex-row sm:flex-wrap sm:items-center [&>a]:w-full [&>button]:w-full [&>form]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto sm:[&>form]:w-auto [&>form>button]:w-full',
    'sticky bottom-0 z-20 -mx-4 bg-workspace-surface/95 px-4 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-3 sm:static sm:mx-0 sm:bg-transparent sm:px-0 sm:pb-0 sm:pt-5' => $mobileSticky,
]) }}>
    {{ $slot }}
</div>
