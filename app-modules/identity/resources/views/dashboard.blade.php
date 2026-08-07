<div wire:poll.30s>
    <x-ui.page-header :title="__('app.dashboard')" :subtitle="__('identity::messages.dashboard_welcome')" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" wire:loading.class="opacity-60">
        <x-ui.stat-card :label="__('identity::messages.active_customers')" :value="number_format($metrics['customers'])" />
        <x-ui.stat-card :label="__('identity::messages.active_projects')" :value="number_format($metrics['projects'])" />
        <x-ui.stat-card :label="__('identity::messages.open_tasks')" :value="number_format($metrics['open_tasks'])" />
        <x-ui.stat-card :label="__('identity::messages.open_tickets')" :value="number_format($metrics['open_tickets'])" />
    </div>
</div>
