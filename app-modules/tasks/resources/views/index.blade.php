<div>
    <x-ui.page-header :title="__('tasks::messages.tasks')">
        <x-slot:actions>
            @can('tasks.create')
                <x-ui.button :href="route('tasks.create', ['project' => $projectId])" wire:navigate>{{ __('tasks::messages.new_task') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('tasks::messages.search_placeholder')" />
        </div>
        <span class="pb-2 text-xs text-slate-500" wire:loading wire:target="q">{{ __('app.loading') }}</span>
    </x-ui.filter-bar>

    @php($grouped = collect($tasks)->groupBy('status'))

    <div class="grid gap-4 xl:grid-cols-5" wire:loading.class="opacity-60" wire:target="q">
        @foreach($statuses as $statusItem)
            <section class="min-w-0 rounded-2xl border border-slate-200 bg-slate-100/80 p-3" wire:key="task-column-{{ $statusItem->value }}">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="font-bold text-slate-900">{{ __('tasks::messages.status.'.$statusItem->value) }}</h2>
                    <x-ui.badge>{{ $grouped->get($statusItem->value, collect())->count() }}</x-ui.badge>
                </div>

                <div class="space-y-3">
                    @forelse($grouped->get($statusItem->value, collect()) as $task)
                        <a href="{{ route('tasks.show', $task['id']) }}" wire:navigate class="group block" wire:key="task-{{ $task['id'] }}">
                            <x-ui.card class="transition group-hover:-translate-y-0.5 group-hover:border-slate-300 group-hover:shadow-md">
                                <div class="font-bold text-slate-950">{{ $task['title'] }}</div>
                                <div class="mt-2 text-xs font-medium text-slate-500">{{ $task['project_title'] }}</div>
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    <x-ui.badge>{{ __('tasks::messages.priority.'.$task['priority']) }}</x-ui.badge>
                                    <x-ui.badge>{{ $task['assignee_name'] ?: __('tasks::messages.unassigned') }}</x-ui.badge>
                                </div>

                                @if($task['due_at'])
                                    <div class="mt-3 text-xs text-slate-500" dir="ltr">{{ \Illuminate\Support\Carbon::parse($task['due_at'])->format('Y-m-d H:i') }}</div>
                                @endif
                            </x-ui.card>
                        </a>
                    @empty
                        <x-ui.empty-state :description="__('app.no_records')" class="px-3 py-5" />
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
