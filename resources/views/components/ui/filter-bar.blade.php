@props(['action' => null, 'livewire' => false])

@if($livewire)
    <div {{ $attributes->class(['mb-5 flex w-full flex-col gap-3 sm:mb-6 sm:flex-row sm:items-end [&>*]:min-w-0 [&>a]:w-full [&>button]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto']) }}>
        {{ $slot }}
    </div>
@else
    <form method="GET" action="{{ $action ?: url()->current() }}" {{ $attributes->class(['mb-5 flex w-full flex-col gap-3 sm:mb-6 sm:flex-row sm:items-end [&>*]:min-w-0 [&>a]:w-full [&>button]:w-full sm:[&>a]:w-auto sm:[&>button]:w-auto']) }}>
        {{ $slot }}
    </form>
@endif
