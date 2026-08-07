<div>
    <x-ui.page-header :title="__('tickets::messages.tickets')">
        <x-slot:actions>
            @can('tickets.create')
                <x-ui.button :href="route('tickets.create', ['project' => $projectId])" wire:navigate>{{ __('tickets::messages.new_ticket') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('tickets::messages.search_placeholder')" />
        </div>
        <span class="pb-2 text-xs text-slate-500" wire:loading wire:target="q">{{ __('app.loading') }}</span>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q">
        <thead>
            <tr>
                <th>{{ __('tickets::messages.subject') }}</th>
                <th>{{ __('tickets::messages.customer') }}</th>
                <th>{{ __('tickets::messages.project') }}</th>
                <th>{{ __('tickets::messages.category') }}</th>
                <th>{{ __('tickets::messages.priority') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('tickets::messages.assignee') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr wire:key="ticket-{{ $ticket['id'] }}" class="transition hover:bg-slate-50">
                    <td class="font-semibold"><a href="{{ route('tickets.show', $ticket['id']) }}" wire:navigate class="block py-1">#{{ $ticket['id'] }} — {{ $ticket['subject'] }}</a></td>
                    <td>{{ $ticket['customer_name'] }}</td>
                    <td>{{ $ticket['project_title'] ?: __('tickets::messages.no_project') }}</td>
                    <td><x-ui.badge>{{ __('tickets::messages.category.'.$ticket['category']) }}</x-ui.badge></td>
                    <td><x-ui.badge>{{ __('tickets::messages.priority.'.$ticket['priority']) }}</x-ui.badge></td>
                    <td><x-ui.badge>{{ __('tickets::messages.status.'.$ticket['status']) }}</x-ui.badge></td>
                    <td>{{ $ticket['assignee_name'] ?: __('tickets::messages.unassigned') }}</td>
                </tr>
            @empty
                <x-ui.empty-row colspan="7" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
