<div>
    <x-ui.page-header :title="__('tickets::messages.tickets')">
        <x-slot:actions>
            @can('tickets.create')
                <x-ui.button :href="route('tickets.create', ['project' => $projectId])" icon="fa-plus" wire:navigate>{{ __('tickets::messages.new_ticket') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('tickets::messages.search_placeholder')" />
        </div>
        <span class="pb-2 text-xs text-slate-500" wire:loading wire:target="q"><i class="fa-light fa-spinner-third fa-spin ml-1" aria-hidden="true"></i>{{ __('app.loading') }}</span>
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
                    <td class="font-semibold">
                        <a href="{{ route('tickets.show', $ticket['id']) }}" wire:navigate class="flex items-center gap-2 py-1 text-slate-900 transition hover:text-slate-600">
                            <i class="fa-light fa-ticket shrink-0 text-slate-400" aria-hidden="true"></i>
                            <span>#{{ $ticket['id'] }} — {{ $ticket['subject'] }}</span>
                        </a>
                    </td>
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
