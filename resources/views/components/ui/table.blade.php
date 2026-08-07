<div {{ $attributes->class(['overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm']) }}>
    <table class="table min-w-full divide-y divide-slate-200 text-sm">
        {{ $slot }}
    </table>
</div>
