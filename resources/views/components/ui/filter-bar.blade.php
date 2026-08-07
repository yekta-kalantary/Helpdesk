@props(['action' => null, 'livewire' => false])

@if($livewire)
    <div {{ $attributes->class(['mb-6 flex flex-col gap-3 sm:flex-row sm:items-end']) }}>
        {{ $slot }}
    </div>
@else
    <form method="GET" action="{{ $action ?: url()->current() }}" {{ $attributes->class(['mb-6 flex flex-col gap-3 sm:flex-row sm:items-end']) }}>
        {{ $slot }}
    </form>
@endif
