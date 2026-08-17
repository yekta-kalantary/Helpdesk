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

    <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="فیلترهای سریع">
        <span class="text-caption font-bold text-text-muted">فیلتر سریع</span>
        <button type="button" wire:click="$set('status', 'active')" aria-pressed="{{ $status === 'active' ? 'true' : 'false' }}" @class([
            'min-h-11 rounded-full border px-3 py-1.5 text-caption font-bold transition',
            'border-primary bg-primary-surface text-primary-text' => $status === 'active',
            'border-border bg-surface text-text-muted hover:border-primary hover:text-primary-text' => $status !== 'active',
        ])>فعال</button>
        <button type="button" wire:click="$set('status', 'completed')" aria-pressed="{{ $status === 'completed' ? 'true' : 'false' }}" @class([
            'min-h-11 rounded-full border px-3 py-1.5 text-caption font-bold transition',
            'border-primary bg-primary-surface text-primary-text' => $status === 'completed',
            'border-border bg-surface text-text-muted hover:border-primary hover:text-primary-text' => $status !== 'completed',
        ])>تکمیل‌شده</button>
    </div>

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

    <div class="space-y-2 sm:hidden" data-project-list="rows">
        @forelse($projects as $project)
            <article wire:key="project-mobile-{{ $project->id }}" data-project-id="{{ $project->id }}" data-status="{{ $project->status->value }}" data-count-members="{{ $project->members_count }}" data-count-tasks="{{ $project->tasks_count }}" class="rounded-workspace border border-workspace-border bg-workspace-surface">
                <a href="{{ route('projects.show', $project) }}" wire:navigate class="group block min-h-11 rounded-workspace p-4 transition-colors duration-150 hover:bg-workspace-info-surface focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="min-w-0 truncate font-bold text-workspace-text group-hover:text-workspace-teal">{{ $project->name }}</span>
                        <x-ui.badge :tone="$project->status->value === 'active' ? 'success' : 'neutral'">{{ $project->status->value === 'active' ? 'فعال' : 'تکمیل‌شده' }}</x-ui.badge>
                    </div>
                    <div class="mt-1 text-sm text-workspace-muted">{{ $project->client->name }}</div>
                    <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-workspace-muted">
                        <div><dt class="inline">اعضا: </dt><dd class="inline font-bold text-workspace-text">{{ $project->members_count }}</dd></div>
                        <div><dt class="inline">تسک‌ها: </dt><dd class="inline font-bold text-workspace-text">{{ $project->tasks_count }}</dd></div>
                        <div><dt class="inline">آخرین تغییر: </dt><dd class="inline"><x-ui.date :value="$project->updated_at" /></dd></div>
                    </dl>
                </a>
            </article>
        @empty
            <x-ui.card><div data-empty-state="projects" class="text-center text-sm text-workspace-muted">پروژه‌ای پیدا نشد.@if($isAdmin) <a class="inline-flex min-h-11 items-center rounded-md font-bold text-workspace-teal transition-colors duration-150 hover:underline focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2" href="{{ route('projects.create') }}" wire:navigate>پروژه جدید بسازید.</a>@endif</div></x-ui.card>
        @endforelse
    </div>

    <div class="mt-4 hidden sm:block">
        <x-ui.table data-project-table="comparison" wire:loading.class="opacity-60" wire:target="q,status,client">
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
                            <a href="{{ route('projects.show', $project) }}" wire:navigate class="inline-flex min-h-11 items-center rounded-md font-bold text-workspace-text transition-colors duration-150 hover:text-workspace-teal hover:underline focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2">{{ $project->name }}</a>
                            @if($project->description)<div class="mt-1 max-w-xl truncate text-xs text-workspace-muted">{{ $project->description }}</div>@endif
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
