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
        <div class="grid w-full gap-3 md:grid-cols-3">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('projects::messages.search_placeholder')" />
            <x-ui.select name="status" wire:model.live="status">
                <option value="">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="completed">تکمیل‌شده</option>
            </x-ui.select>
            @if($isAdmin)
                <x-ui.select name="client" wire:model.live="client">
                    <option value="">همه مشتریان</option>
                    @foreach($clients as $clientItem)
                        <option value="{{ $clientItem->id }}">{{ $clientItem->name }}</option>
                    @endforeach
                </x-ui.select>
            @endif
        </div>
    </x-ui.filter-bar>

    <div class="overflow-x-auto">
        <x-ui.table wire:loading.class="opacity-60" wire:target="q,status,client">
            <thead>
                <tr>
                    <th>پروژه</th>
                    @if($isAdmin)<th>مشتری</th>@endif
                    <th>اعضا</th>
                    <th>تسک‌ها</th>
                    <th>وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr wire:key="project-{{ $project->id }}">
                        <td>
                            <a href="{{ route('projects.show', $project) }}" wire:navigate class="font-bold text-slate-950 hover:underline">{{ $project->name }}</a>
                            @if($project->description)<div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $project->description }}</div>@endif
                        </td>
                        @if($isAdmin)<td>{{ $project->client->name }}</td>@endif
                        <td>{{ $project->members_count }}</td>
                        <td>{{ $project->tasks_count }}</td>
                        <td><x-ui.badge :tone="$project->status->value === 'active' ? 'success' : 'neutral'">{{ $project->status->value === 'active' ? 'فعال' : 'تکمیل‌شده' }}</x-ui.badge></td>
                    </tr>
                @empty
                    <x-ui.empty-row :colspan="$isAdmin ? 5 : 4" />
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-5">{{ $projects->links() }}</div>
</div>
