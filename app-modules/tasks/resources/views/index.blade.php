<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('tasks::messages.tasks')">
        @if($isAdmin)
            <x-slot:actions>
                <x-ui.button :href="route('tasks.create', ['project' => $projectId])" icon="fa-plus" wire:navigate>{{ __('tasks::messages.new_task') }}</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('tasks::messages.search_placeholder')" />
        </div>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q,delete">
        <thead>
            <tr>
                <th>{{ __('app.title') }}</th>
                <th>{{ __('tasks::messages.project') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                <tr wire:key="task-{{ $task->id }}">
                    <td>
                        <div class="font-semibold">{{ $task->title }}</div>
                        @if($task->description)
                            <div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $task->description }}</div>
                        @endif
                    </td>
                    <td>{{ $task->project->title }}</td>
                    <td>
                        <x-ui.badge :tone="$task->is_done ? 'success' : 'neutral'">
                            {{ $task->is_done ? 'انجام شده' : 'باز' }}
                        </x-ui.badge>
                    </td>
                    <td>
                        @if($isAdmin)
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button size="sm" variant="secondary" :href="route('tasks.edit', $task->id)" icon="fa-pen-to-square" wire:navigate>{{ __('app.edit') }}</x-ui.button>
                                <x-ui.button size="sm" variant="danger" icon="fa-trash" wire:click="delete({{ $task->id }})" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="delete({{ $task->id }})">{{ __('app.delete') }}</x-ui.button>
                            </div>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="4" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
