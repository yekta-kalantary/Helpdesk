<div class="overflow-x-clip">
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('tasks::messages.tasks')">
        <x-slot:actions>
            <x-ui.button :href="route('tasks.create', ['project' => $project ?: null])" icon="fa-plus" wire:navigate>{{ __('tasks::messages.new_task') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="فیلترهای سریع">
        <span class="text-label font-bold text-text-muted">فیلترهای سریع</span>
        <button type="button" wire:click="$set('overdue', '1')" aria-pressed="{{ $overdue === '1' ? 'true' : 'false' }}" @class([
            'ui-filter-chip',
            'border-warning bg-warning-surface text-warning-text' => $overdue === '1',
            'bg-surface' => $overdue !== '1',
        ])>فقط عقب‌افتاده</button>
        <button type="button" wire:click="$set('assignee', '{{ auth()->id() }}')" aria-pressed="{{ $assignee === (string) auth()->id() ? 'true' : 'false' }}" @class([
            'ui-filter-chip',
            'border-primary bg-info-surface text-info-text' => $assignee === (string) auth()->id(),
            'bg-surface' => $assignee !== (string) auth()->id(),
        ])>مسئول من</button>
        <button type="button" wire:click="$set('assignee', 'unassigned')" aria-pressed="{{ $assignee === 'unassigned' ? 'true' : 'false' }}" @class([
            'ui-filter-chip',
            'border-primary bg-info-surface text-info-text' => $assignee === 'unassigned',
            'bg-surface' => $assignee !== 'unassigned',
        ])>بدون مسئول</button>
        @foreach($priorities as $priorityItem)
            <button type="button" wire:click="$set('priority', '{{ $priorityItem->value }}')" aria-pressed="{{ $priority === $priorityItem->value ? 'true' : 'false' }}" @class([
                'ui-filter-chip',
                'border-primary bg-info-surface text-info-text' => $priority === $priorityItem->value,
                'bg-surface' => $priority !== $priorityItem->value,
            ])>{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</button>
        @endforeach
        <button type="button" wire:click="$set('sort', 'due_asc')" aria-pressed="{{ $sort === 'due_asc' ? 'true' : 'false' }}" @class([
            'ui-filter-chip',
            'border-primary bg-info-surface text-info-text' => $sort === 'due_asc',
            'bg-surface' => $sort !== 'due_asc',
        ])>موعد نزدیک‌تر</button>
    </div>

    <x-ui.filter-bar :livewire="true" mobile-label="فیلترهای تسک" :active-count="collect([$q, $project, $status, $priority, $assignee, $overdue, $sort !== 'updated_desc' ? $sort : ''])->filter(fn ($value) => filled($value))->count()">
        <x-slot:desktop>
            <div class="grid w-full gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <x-ui.input id="task-q-desktop" name="q" label="جستجو" :value="$q" wire:model.live.debounce.300ms="q" placeholder="عنوان یا Reference" />
                <x-ui.select id="task-project-desktop" name="project" label="پروژه" wire:model.live="project">
                    <option value="">همه پروژه‌ها</option>
                    @foreach($projects as $projectItem)<option value="{{ $projectItem->id }}">{{ $projectItem->name }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-status-desktop" name="status" label="وضعیت" wire:model.live="status" :disabled="$project === ''">
                    <option value="">{{ $project === '' ? 'برای فیلتر وضعیت، پروژه را انتخاب کنید' : 'همه وضعیت‌های پروژه' }}</option>
                    @foreach($statuses as $statusItem)<option value="{{ $statusItem->id }}">{{ $statusItem->title }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-priority-desktop" name="priority" label="اولویت" wire:model.live="priority">
                    <option value="">همه اولویت‌ها</option>
                    @foreach($priorities as $priorityItem)<option value="{{ $priorityItem->value }}">{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-assignee-desktop" name="assignee" label="مسئول" wire:model.live="assignee">
                    <option value="">همه مسئول‌ها</option>
                    @foreach($assignees as $assigneeItem)<option value="{{ $assigneeItem->id }}">{{ $assigneeItem->full_name }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-overdue-desktop" name="overdue" label="سررسید" wire:model.live="overdue">
                    <option value="">همه سررسیدها</option>
                    <option value="1">فقط عقب‌افتاده</option>
                </x-ui.select>
                <x-ui.select id="task-sort-desktop" name="sort" label="مرتب‌سازی" wire:model.live="sort">
                    <option value="updated_desc">آخرین بروزرسانی</option>
                    <option value="due_asc">موعد نزدیک‌تر</option>
                    <option value="due_desc">موعد دورتر</option>
                </x-ui.select>
            </div>
        </x-slot:desktop>
        <x-slot:mobile>
            <div class="grid w-full gap-3 border-t border-border pt-3">
                <x-ui.input id="task-q-mobile" name="q" label="جستجو" :value="$q" wire:model.live.debounce.300ms="q" placeholder="عنوان یا Reference" />
                <x-ui.select id="task-project-mobile" name="project" label="پروژه" wire:model.live="project">
                    <option value="">همه پروژه‌ها</option>
                    @foreach($projects as $projectItem)<option value="{{ $projectItem->id }}">{{ $projectItem->name }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-status-mobile" name="status" label="وضعیت" wire:model.live="status" :disabled="$project === ''">
                    <option value="">{{ $project === '' ? 'برای فیلتر وضعیت، پروژه را انتخاب کنید' : 'همه وضعیت‌های پروژه' }}</option>
                    @foreach($statuses as $statusItem)<option value="{{ $statusItem->id }}">{{ $statusItem->title }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-priority-mobile" name="priority" label="اولویت" wire:model.live="priority">
                    <option value="">همه اولویت‌ها</option>
                    @foreach($priorities as $priorityItem)<option value="{{ $priorityItem->value }}">{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-assignee-mobile" name="assignee" label="مسئول" wire:model.live="assignee">
                    <option value="">همه مسئول‌ها</option>
                    @foreach($assignees as $assigneeItem)<option value="{{ $assigneeItem->id }}">{{ $assigneeItem->full_name }}</option>@endforeach
                </x-ui.select>
                <x-ui.select id="task-overdue-mobile" name="overdue" label="سررسید" wire:model.live="overdue">
                    <option value="">همه سررسیدها</option>
                    <option value="1">فقط عقب‌افتاده</option>
                </x-ui.select>
                <x-ui.select id="task-sort-mobile" name="sort" label="مرتب‌سازی" wire:model.live="sort">
                    <option value="updated_desc">آخرین بروزرسانی</option>
                    <option value="due_asc">موعد نزدیک‌تر</option>
                    <option value="due_desc">موعد دورتر</option>
                </x-ui.select>
            </div>
        </x-slot:mobile>
    </x-ui.filter-bar>

    <div class="space-y-3 lg:hidden" wire:loading.class="opacity-60" wire:target="q,project,status,priority,assignee,overdue,sort" data-task-list>
        @forelse($tasks as $task)
            <x-ui.card wire:key="task-card-{{ $task->id }}" padding="false" class="overflow-hidden">
                <a href="{{ route('tasks.show', $task) }}" wire:navigate class="ui-list-action ui-list-row block">
                    <div class="flex items-start justify-between gap-3" data-task-row>
                        <div class="min-w-0">
                            <p dir="ltr" class="text-caption font-bold text-info-text">{{ $task->reference }}</p>
                            <h2 class="mt-1 break-words font-bold leading-6 text-text">{{ $task->title }}</h2>
                        </div>
                        <x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2" aria-label="متادیتای تسک">
                        <x-ui.badge :tone="$task->priority === \Modules\Tasks\Domain\Enums\TaskPriority::High ? 'warning' : 'neutral'">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</x-ui.badge>
                        <x-ui.badge :tone="$task->assignee ? 'info' : 'neutral'">{{ $task->assignee ? 'مسئول دارد' : 'بدون مسئول' }}</x-ui.badge>
                        @if($task->due_date && $task->due_date->isBefore(today()) && !$task->isDone())
                            <x-ui.badge tone="danger">عقب‌افتاده</x-ui.badge>
                        @endif
                    </div>
                    <div class="ui-list-meta mt-4 grid grid-cols-2 gap-x-4 gap-y-3">
                        <div><p class="text-caption text-text-muted">پروژه</p><p class="mt-1 font-semibold text-text">{{ $task->project->name }}</p></div>
                        <div><p class="text-caption text-text-muted">گروه کاری</p><p class="mt-1 font-semibold text-text">{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</p></div>
                        <div><p class="text-caption text-text-muted">اولویت</p><p class="mt-1 font-semibold text-text">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</p></div>
                        <div><p class="text-caption text-text-muted">مسئول</p><p class="mt-1 font-semibold text-text">{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</p></div>
                        <div><p class="text-caption text-text-muted">موعد</p><p @class(['mt-1 font-semibold text-danger-text' => $task->due_date && $task->due_date->isBefore(today()) && !$task->isDone(), 'mt-1 font-semibold text-text' => !($task->due_date && $task->due_date->isBefore(today()) && !$task->isDone())])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}</p></div>
                        <div><p class="text-caption text-text-muted">بروزرسانی</p><p class="mt-1 font-semibold text-text"><x-ui.date :value="$task->updated_at" datetime /></p></div>
                    </div>
                </a>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="تسکی پیدا نشد" />
        @endforelse
    </div>

    <div class="hidden lg:block" wire:loading.class="opacity-60" wire:target="q,project,status,priority,assignee,overdue,sort" data-task-list>
        <div class="rounded-surface border border-border bg-surface">
            @forelse($tasks as $task)
                <article class="ui-list-row ui-list-divider group" wire:key="task-{{ $task->id }}" data-task-row>
                    <div class="flex items-start justify-between gap-6">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span dir="ltr" class="text-caption font-bold text-info-text">{{ $task->reference }}</span>
                                <x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge>
                                <x-ui.badge :tone="$task->priority === \Modules\Tasks\Domain\Enums\TaskPriority::High ? 'warning' : 'neutral'">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</x-ui.badge>
                                <x-ui.badge :tone="$task->assignee ? 'info' : 'neutral'">{{ $task->assignee ? 'مسئول دارد' : 'بدون مسئول' }}</x-ui.badge>
                                @if($task->due_date && $task->due_date->isBefore(today()) && !$task->isDone())<x-ui.badge tone="danger">عقب‌افتاده</x-ui.badge>@endif
                            </div>
                            <a href="{{ route('tasks.show', $task) }}" wire:navigate class="mt-2 flex min-h-11 items-center break-words rounded-control text-body font-bold leading-7 text-text hover:text-primary hover:underline">{{ $task->title }}</a>
                            @if($task->description)<p class="mt-1 max-w-3xl truncate text-body-sm text-text-muted">{{ $task->description }}</p>@endif
                        </div>
                        <div class="shrink-0 text-left text-caption text-text-muted">
                            <p>بروزرسانی</p>
                            <p class="mt-1 font-semibold text-text"><x-ui.date :value="$task->updated_at" datetime /></p>
                        </div>
                    </div>
                    <dl class="ui-list-meta mt-4 flex flex-wrap gap-x-8 gap-y-2" aria-label="متادیتای تسک">
                        <div><dt class="inline text-caption text-text-muted">پروژه: </dt><dd class="inline font-semibold text-text">{{ $task->project->name }}</dd></div>
                        <div><dt class="inline text-caption text-text-muted">گروه کاری: </dt><dd class="inline font-semibold text-text">{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</dd></div>
                        <div><dt class="inline text-caption text-text-muted">مسئول: </dt><dd class="inline font-semibold text-text">{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</dd></div>
                        <div><dt class="inline text-caption text-text-muted">موعد: </dt><dd @class(['inline font-semibold text-danger-text' => $task->due_date && $task->due_date->isBefore(today()) && !$task->isDone(), 'inline font-semibold text-text' => !($task->due_date && $task->due_date->isBefore(today()) && !$task->isDone())])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}</dd></div>
                    </dl>
                </article>
            @empty
                <x-ui.empty-state title="تسکی پیدا نشد" />
            @endforelse
        </div>
    </div>

    <div class="mt-5">{{ $tasks->links() }}</div>
</div>
