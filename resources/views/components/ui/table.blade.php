<div {{ $attributes->class(['ui-table-shell overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm']) }}>
    <table class="ui-table min-w-full text-sm">
        {{ $slot }}
    </table>
</div>
