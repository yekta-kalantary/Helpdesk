@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<header {{ $attributes->class(['mb-5 flex flex-col gap-4 sm:mb-6 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        @if($breadcrumbs)
            <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-2" />
        @endif
        <h1 class="break-words text-xl font-black tracking-tight text-slate-950 sm:text-2xl">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 max-w-3xl break-words text-sm leading-6 text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($primary)
        <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
            {{ $primary }}
        </div>
    @endisset

    @isset($actions)
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center [&>a]:w-full [&>button]:w-full [&>form]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto sm:[&>form]:w-auto [&>form>button]:w-full">
            {{ $actions }}
        </div>
    @endisset
</header>
