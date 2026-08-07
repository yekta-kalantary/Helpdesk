<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('projects::messages.projects')">
        <x-slot:actions>
            @can('projects.create')
                <x-ui.button :href="route('projects.create')" wire:navigate>{{ __('projects::messages.new_project') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('projects::messages.search_placeholder')" />
        </div>
        <span class="pb-2 text-xs text-slate-500" wire:loading wire:target="q">{{ __('app.loading') }}</span>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q,delete">
        <thead>
            <tr>
                <th>{{ __('app.title') }}</th>
                <th>{{ __('projects::messages.category') }}</th>
                <th>{{ __('projects::messages.customer') }}</th>
                <th>{{ __('projects::messages.type') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('projects::messages.progress') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $project)
                <tr wire:key="project-{{ $project['id'] }}">
                    <td class="font-semibold">{{ $project['title'] }}</td>
                    <td><x-ui.badge>{{ __('projects::messages.category.'.$project['category']) }}</x-ui.badge></td>
                    <td>{{ $project['customer_name'] ?: '—' }}</td>
                    <td><x-ui.badge>{{ __('projects::messages.type.'.$project['type']) }}</x-ui.badge></td>
                    <td><x-ui.badge>{{ __('projects::messages.status.'.$project['status']) }}</x-ui.badge></td>
                    <td><x-ui.progress class="w-32" :value="$project['progress']" /></td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            @can('tasks.view')
                                <x-ui.button size="sm" variant="secondary" :href="route('tasks.index', ['project' => $project['id']])" wire:navigate>{{ __('app.tasks') }}</x-ui.button>
                            @endcan
                            @can('tickets.view')
                                <x-ui.button size="sm" variant="secondary" :href="route('tickets.index', ['project' => $project['id']])" wire:navigate>{{ __('app.tickets') }}</x-ui.button>
                            @endcan
                            @can('projects.update')
                                <x-ui.button size="sm" variant="secondary" :href="route('projects.edit', $project['id'])" wire:navigate>{{ __('app.edit') }}</x-ui.button>
                            @endcan
                            @can('projects.delete')
                                <x-ui.button size="sm" variant="danger" wire:click="delete({{ $project['id'] }})" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="delete({{ $project['id'] }})">{{ __('app.delete') }}</x-ui.button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="7" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
