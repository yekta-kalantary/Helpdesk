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
        <button type="button" wire:click="$set('overdue', '1')" @class([
            'rounded-full border px-3 py-1.5 text-xs font-bold transition',
            'border-amber-300 bg-amber-50 text-amber-800' => $overdue === '1',
            'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $overdue !== '1',
        ])>فقط عقب‌افتاده</button>
        @foreach($priorities as $priorityItem)
            <button type="button" wire:click="$set('priority', '{{ $priorityItem->value }}')" @class([
                'rounded-full border px-3 py-1.5 text-xs font-bold transition',
                'border-teal-300 bg-teal-50 text-teal-800' => $priority === $priorityItem->value,
                'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $priority !== $priorityItem->value,
            ])>{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</button>
        @endforeach
        <button type="button" wire:click="$set('sort', 'due_asc')" @class([
            'rounded-full border px-3 py-1.5 text-xs font-bold transition',
            'border-teal-300 bg-teal-50 text-teal-800' => $sort === 'due_asc',
            'border-workspace-border bg-workspace-surface text-slate-600 hover:border-teal-300 hover:text-teal-800' => $sort !== 'due_asc',
        ])>موعد نزدیک‌تر</button>
    </div>

    <x-ui.filter-bar :livewire="true" mobile-label="فیلترهای تسک">
        <div class="grid w-full gap-3 sm:grid-cols-2 xl:grid-cols-4">
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

    <div class="space-y-3 lg:hidden" wire:loading.class="opacity-60" wire:target="q,project,status,priority,assignee,overdue,sort">
        @forelse($tasks as $task)
            <x-ui.card wire:key="task-card-{{ $task->id }}" padding="false" class="overflow-hidden">
                <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block p-4 transition hover:bg-teal-50/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-teal-700">{{ $task->reference }}</p>
                            <h2 class="mt-1 break-words font-bold leading-6 text-slate-950">{{ $task->title }}</h2>
                        </div>
                        <x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge>
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

    <div class="hidden lg:block">
        <x-ui.table wire:loading.class="opacity-60" wire:target="q,project,status,priority,assignee,overdue,sort">
            <thead>
                <tr>
                    <th>Reference / عنوان</th>
                    <th>پروژه</th>
                    <th>وضعیت</th>
                    <th>گروه کاری</th>
                    <th>اولویت</th>
                    <th>مسئول</th>
                    <th>موعد</th>
                    <th>بروزرسانی</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr wire:key="task-{{ $task->id }}">
                        <td>
                            <a href="{{ route('tasks.show', $task) }}" wire:navigate class="font-bold text-slate-950 hover:underline">{{ $task->reference }} · {{ $task->title }}</a>
                            @if($task->description)<div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $task->description }}</div>@endif
                        </td>
                        <td>{{ $task->project->name }}</td>
                        <td><x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge></td>
                        <td>{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</td>
                        <td>{{ __('tasks::messages.priorities.'.$task->priority->value) }}</td>
                        <td>{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</td>
                        <td @class(['font-bold text-red-600' => $task->due_date && $task->due_date->isBefore(today()) && !$task->isDone()])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}</td>
                        <td><x-ui.date :value="$task->updated_at" datetime /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="8" />
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-5">{{ $tasks->links() }}</div>
</div>
