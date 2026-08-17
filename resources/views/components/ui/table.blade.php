<div {{ $attributes->class(['ui-table-shell overflow-x-auto rounded-surface border border-border bg-surface shadow-subtle']) }}>
    <table class="ui-table min-w-full text-sm">
        {{ $slot }}
    </table>
</div>
