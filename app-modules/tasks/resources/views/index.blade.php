<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('tasks::messages.tasks')">
        <x-slot:actions>
            <x-ui.button :href="route('tasks.create', ['project' => $project ?: null])" icon="fa-plus" wire:navigate>{{ __('tasks::messages.new_task') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="فیلترهای سریع">
        <span class="text-xs font-bold text-slate-500">فیلترهای سریع</span>
        <button type="button" wire:click="$set('overdue', '1')" aria-pressed="{{ $overdue === '1' ? 'true' : 'false' }}" @class([
            'min-h-11 rounded-full border px-3 py-1.5 text-xs font-bold transition',
            'border-amber-300 bg-amber-50 text-amber-800' => $overdue === '1',
            'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $overdue !== '1',
        ])>فقط عقب‌افتاده</button>
        <button type="button" wire:click="$set('assignee', '{{ auth()->id() }}')" aria-pressed="{{ $assignee === (string) auth()->id() ? 'true' : 'false' }}" @class([
            'min-h-11 rounded-full border px-3 py-1.5 text-xs font-bold transition',
            'border-teal-300 bg-teal-50 text-teal-800' => $assignee === (string) auth()->id(),
            'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $assignee !== (string) auth()->id(),
        ])>مسئول من</button>
        <button type="button" wire:click="$set('assignee', 'unassigned')" aria-pressed="{{ $assignee === 'unassigned' ? 'true' : 'false' }}" @class([
            'min-h-11 rounded-full border px-3 py-1.5 text-xs font-bold transition',
            'border-teal-300 bg-teal-50 text-teal-800' => $assignee === 'unassigned',
            'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $assignee !== 'unassigned',
        ])>بدون مسئول</button>
        @foreach($priorities as $priorityItem)
            <button type="button" wire:click="$set('priority', '{{ $priorityItem->value }}')" aria-pressed="{{ $priority === $priorityItem->value ? 'true' : 'false' }}" @class([
                'min-h-11 rounded-full border px-3 py-1.5 text-xs font-bold transition',
                'border-teal-300 bg-teal-50 text-teal-800' => $priority === $priorityItem->value,
                'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $priority !== $priorityItem->value,
            ])>{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</button>
        @endforeach
        <button type="button" wire:click="$set('sort', 'due_asc')" aria-pressed="{{ $sort === 'due_asc' ? 'true' : 'false' }}" @class([
            'min-h-11 rounded-full border px-3 py-1.5 text-xs font-bold transition',
            'border-teal-300 bg-teal-50 text-teal-800' => $sort === 'due_asc',
            'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $sort !== 'due_asc',
        ])>موعد نزدیک‌تر</button>
    </div>

    <x-ui.filter-bar :livewire="true" mobile-label="فیلترهای تسک" :active-count="collect([$q, $project, $status, $priority, $assignee, $overdue, $sort !== 'updated_desc' ? $sort : ''])->filter(fn ($value) => filled($value))->count()">
        <div class="grid w-full gap-3 border-t border-slate-100 pt-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" placeholder="جستجو با عنوان یا Reference" />
            <x-ui.select name="project" wire:model.live="project">
                <option value="">همه پروژه‌ها</option>
                @foreach($projects as $projectItem)<option value="{{ $projectItem->id }}">{{ $projectItem->name }}</option>@endforeach
            </x-ui.select>
            <x-ui.select name="status" wire:model.live="status" :disabled="$project === ''">
                <option value="">{{ $project === '' ? 'برای فیلتر وضعیت، پروژه را انتخاب کنید' : 'همه وضعیت‌های پروژه' }}</option>
                @foreach($statuses as $statusItem)<option value="{{ $statusItem->id }}">{{ $statusItem->title }}</option>@endforeach
            </x-ui.select>
            <x-ui.select name="priority" wire:model.live="priority">
                <option value="">همه اولویت‌ها</option>
                @foreach($priorities as $priorityItem)<option value="{{ $priorityItem->value }}">{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</option>@endforeach
            </x-ui.select>
            <x-ui.select name="assignee" wire:model.live="assignee">
                <option value="">همه مسئول‌ها</option>
                @foreach($assignees as $assigneeItem)<option value="{{ $assigneeItem->id }}">{{ $assigneeItem->full_name }}</option>@endforeach
            </x-ui.select>
            <x-ui.select name="overdue" wire:model.live="overdue">
                <option value="">همه سررسیدها</option>
                <option value="1">فقط عقب‌افتاده</option>
            </x-ui.select>
            <x-ui.select name="sort" wire:model.live="sort">
                <option value="updated_desc">آخرین بروزرسانی</option>
                <option value="due_asc">موعد نزدیک‌تر</option>
                <option value="due_desc">موعد دورتر</option>
            </x-ui.select>
        </div>
    </x-ui.filter-bar>

    <div class="space-y-3 lg:hidden" wire:loading.class="opacity-60" wire:target="q,project,status,priority,assignee,overdue,sort" data-task-list>
        @forelse($tasks as $task)
            <x-ui.card wire:key="task-card-{{ $task->id }}" padding="false" class="overflow-hidden">
                <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block p-4 transition hover:bg-teal-50/40">
                    <div class="flex items-start justify-between gap-3" data-task-row>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-teal-700">{{ $task->reference }}</p>
                            <h2 class="mt-1 break-words font-bold leading-6 text-slate-950">{{ $task->title }}</h2>
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
                    <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div><p class="text-xs text-slate-500">پروژه</p><p class="mt-1 font-semibold text-slate-800">{{ $task->project->name }}</p></div>
                        <div><p class="text-xs text-slate-500">گروه کاری</p><p class="mt-1 font-semibold text-slate-800">{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</p></div>
                        <div><p class="text-xs text-slate-500">اولویت</p><p class="mt-1 font-semibold text-slate-800">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</p></div>
                        <div><p class="text-xs text-slate-500">مسئول</p><p class="mt-1 font-semibold text-slate-800">{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</p></div>
                        <div><p class="text-xs text-slate-500">موعد</p><p @class(['mt-1 font-semibold text-red-600' => $task->due_date && $task->due_date->isBefore(today()) && !$task->isDone(), 'mt-1 font-semibold text-slate-800' => !($task->due_date && $task->due_date->isBefore(today()) && !$task->isDone())])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}</p></div>
                        <div><p class="text-xs text-slate-500">بروزرسانی</p><p class="mt-1 font-semibold text-slate-800"><x-ui.date :value="$task->updated_at" datetime /></p></div>
                    </div>
                </a>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="تسکی پیدا نشد" />
        @endforelse
    </div>

    <div class="hidden lg:block" wire:loading.class="opacity-60" wire:target="q,project,status,priority,assignee,overdue,sort" data-task-list>
        <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
            @forelse($tasks as $task)
                <article class="group p-5 transition hover:bg-teal-50/30" wire:key="task-{{ $task->id }}" data-task-row>
                    <div class="flex items-start justify-between gap-6">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-teal-700">{{ $task->reference }}</span>
                                <x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge>
                                <x-ui.badge :tone="$task->priority === \Modules\Tasks\Domain\Enums\TaskPriority::High ? 'warning' : 'neutral'">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</x-ui.badge>
                                <x-ui.badge :tone="$task->assignee ? 'info' : 'neutral'">{{ $task->assignee ? 'مسئول دارد' : 'بدون مسئول' }}</x-ui.badge>
                                @if($task->due_date && $task->due_date->isBefore(today()) && !$task->isDone())<x-ui.badge tone="danger">عقب‌افتاده</x-ui.badge>@endif
                            </div>
                            <a href="{{ route('tasks.show', $task) }}" wire:navigate class="mt-2 block break-words text-base font-bold leading-7 text-slate-950 hover:text-teal-800 hover:underline">{{ $task->title }}</a>
                            @if($task->description)<p class="mt-1 max-w-3xl truncate text-sm text-slate-500">{{ $task->description }}</p>@endif
                        </div>
                        <div class="shrink-0 text-left text-xs text-slate-500">
                            <p>بروزرسانی</p>
                            <p class="mt-1 font-semibold text-slate-700"><x-ui.date :value="$task->updated_at" datetime /></p>
                        </div>
                    </div>
                    <dl class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-sm text-slate-600" aria-label="متادیتای تسک">
                        <div><dt class="inline text-xs text-slate-500">پروژه: </dt><dd class="inline font-semibold text-slate-800">{{ $task->project->name }}</dd></div>
                        <div><dt class="inline text-xs text-slate-500">گروه کاری: </dt><dd class="inline font-semibold text-slate-800">{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</dd></div>
                        <div><dt class="inline text-xs text-slate-500">مسئول: </dt><dd class="inline font-semibold text-slate-800">{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</dd></div>
                        <div><dt class="inline text-xs text-slate-500">موعد: </dt><dd @class(['inline font-semibold text-red-600' => $task->due_date && $task->due_date->isBefore(today()) && !$task->isDone(), 'inline font-semibold text-slate-800' => !($task->due_date && $task->due_date->isBefore(today()) && !$task->isDone())])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}</dd></div>
                    </dl>
                </article>
            @empty
                <x-ui.empty-state title="تسکی پیدا نشد" />
            @endforelse
        </div>
    </div>

    <div class="mt-5">{{ $tasks->links() }}</div>
</div>
