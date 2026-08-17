@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<header {{ $attributes->class(['mb-6 flex flex-col gap-workspace sm:mb-8 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        @if($breadcrumbs)
            <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-2" />
        @endif
        <h1 class="break-words text-2xl font-black tracking-tight text-workspace-text sm:text-3xl">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-2 max-w-3xl break-words text-sm leading-6 text-workspace-muted">{{ $subtitle }}</p>
        @endif
    </div>

    @if(! $slot->isEmpty())
        <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
            {{ $slot }}
        </div>
    @endif

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
