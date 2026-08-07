@props(['action' => null])

<form method="GET" action="{{ $action ?: url()->current() }}" {{ $attributes->class(['mb-6 flex flex-col gap-3 sm:flex-row sm:items-end']) }}>
    {{ $slot }}
</form>
