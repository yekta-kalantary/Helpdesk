<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('projects::messages.projects')">
        @if($isAdmin)
            <x-slot:actions>
                <x-ui.button :href="route('projects.create')" icon="fa-plus" wire:navigate>{{ __('projects::messages.new_project') }}</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('projects::messages.search_placeholder')" />
        </div>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q,delete">
        <thead>
            <tr>
                <th>{{ __('app.title') }}</th>
                <th>{{ __('projects::messages.members') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $project)
                <tr wire:key="project-{{ $project['id'] }}">
                    <td>
                        <div class="font-semibold">{{ $project['title'] }}</div>
                        @if($project['description'])
                            <div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $project['description'] }}</div>
                        @endif
                    </td>
                    <td>{{ $project['members_count'] }}</td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button size="sm" variant="secondary" :href="route('tasks.index', ['project' => $project['id']])" icon="fa-list-check" wire:navigate>{{ __('app.tasks') }}</x-ui.button>

                            @if($isAdmin)
                                <x-ui.button size="sm" variant="secondary" :href="route('projects.edit', $project['id'])" icon="fa-pen-to-square" wire:navigate>{{ __('app.edit') }}</x-ui.button>
                                <x-ui.button size="sm" variant="danger" icon="fa-trash" wire:click="delete({{ $project['id'] }})" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="delete({{ $project['id'] }})">{{ __('app.delete') }}</x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="3" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
