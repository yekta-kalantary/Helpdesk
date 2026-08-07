@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}" {{ $attributes->class([
    'flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition',
    'bg-slate-950 text-white shadow-sm' => $active,
    'text-slate-600 hover:bg-slate-100 hover:text-slate-950' => ! $active,
]) }}>
    <span>{{ $slot }}</span>
    @isset($meta){{ $meta }}@endisset
</a>
