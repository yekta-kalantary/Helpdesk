<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    @php($breadcrumbs = $isAdmin
        ? [
            ['label' => 'پروژه‌ها', 'href' => route('projects.index')],
            ['label' => $project->client->name, 'href' => route('clients.show', $project->client)],
            ['label' => $project->name, 'href' => null],
        ]
        : [
            ['label' => 'پروژه‌ها', 'href' => route('projects.index')],
            ['label' => 'پروژه', 'href' => null],
            ['label' => $project->name, 'href' => null],
        ])

    <x-ui.page-header
        :title="$project->name"
        :subtitle="$project->description ? \Illuminate\Support\Str::limit($project->description, 140) : 'فضای کاری پروژه برای هماهنگی، پیگیری و تحویل کار.'"
        :breadcrumbs="$breadcrumbs"
    >
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('tasks.index', ['project' => $project->id])" icon="fa-list-check" wire:navigate>فهرست تسک‌ها</x-ui.button>
            @if($project->status->value === 'active')
                <x-ui.button :href="route('tasks.create', ['project' => $project->id])" icon="fa-plus" wire:navigate>تسک جدید</x-ui.button>
            @endif
            @if($isAdmin)
                <x-ui.button variant="secondary" :href="route('projects.edit', $project)" icon="fa-pen" wire:navigate>ویرایش پروژه</x-ui.button>
                @if($project->status->value === 'active')
                    <x-ui.button variant="secondary" wire:click="complete" wire:loading.attr="disabled" wire:target="complete">تکمیل پروژه</x-ui.button>
                @else
                    <x-ui.button variant="secondary" wire:click="reopen" wire:loading.attr="disabled" wire:target="reopen">بازگشایی پروژه</x-ui.button>
                @endif
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @error('project')<x-ui.alert tone="danger">{{ $message }}</x-ui.alert>@enderror

    @php($progressPercentage = $totalTasksCount > 0 ? (int) round(($completedTasksCount / $totalTasksCount) * 100) : 0)

    <section class="rounded-2xl border border-workspace-border bg-white p-4 shadow-sm sm:p-6" aria-labelledby="project-progress-heading">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 id="project-progress-heading" class="text-lg font-black text-slate-950">نمای کلی پروژه</h2>
                    <x-ui.badge :tone="$project->status->value === 'active' ? 'success' : 'neutral'">{{ $project->status->value === 'active' ? 'فعال' : 'تکمیل‌شده' }}</x-ui.badge>
                </div>
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-500">
                    <span>{{ $openTasksCount }} تسک باز از {{ $totalTasksCount }}</span>
                    <span>{{ $members->total() }} عضو</span>
                    <span>شروع: <x-ui.date :value="$project->start_date" />{{ $project->start_date ? '' : '—' }}</span>
                    <span>موعد: <x-ui.date :value="$project->due_date" />{{ $project->due_date ? '' : '—' }}</span>
                </div>
            </div>
            <div class="w-full max-w-xs" aria-label="پیشرفت پروژه">
                <div class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-500">
                    <span>پیشرفت</span><span>{{ $progressPercentage }}%</span>
                </div>
                <x-ui.progress :value="$progressPercentage" :show-value="false" />
            </div>
        </div>
    </section>

    @if($project->description)
        <x-ui.card>
            <div class="whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ $project->description }}</div>
        </x-ui.card>
    @endif

    <x-ui.section-tabs :tabs="array_merge([
        ['label' => 'Kanban', 'href' => '#kanban', 'active' => true],
        ['label' => 'Tasks', 'href' => '#tasks', 'active' => false],
        ['label' => 'Activity', 'href' => '#activity', 'active' => false],
        ['label' => 'Members', 'href' => '#members', 'active' => false],
    ], $isAdmin ? [['label' => 'Project Management', 'href' => '#project-management', 'active' => false]] : [])" />

    <section id="kanban" class="scroll-mt-6">
    <x-ui.card>
        <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h2 class="font-black">کانبان پروژه</h2>
                <p class="mt-1 text-xs text-slate-500">ستون‌ها مستقیماً از Workflow همین پروژه ساخته می‌شوند.</p>
            </div>
            @if($project->status->value !== 'active')
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm leading-6 text-amber-900" role="status">
                    <strong>این پروژه تکمیل شده و برد فقط خواندنی است.</strong>
                    برای تغییر وضعیت تسک یا ایجاد تسک جدید، ابتدا پروژه را بازگشایی کنید.
                </div>
            @endif
            <div class="grid gap-2 sm:grid-cols-2">
                <label class="block text-sm">
                    <span class="mb-1 block text-xs font-semibold text-slate-500">جستجوی تسک</span>
                    <input wire:model.live.debounce.300ms="taskSearch" type="search" placeholder="عنوان یا Reference" class="min-h-11 w-full rounded-xl border-slate-300 text-sm">
                </label>
                @if($workGroups->isNotEmpty())
                    <label class="block text-sm">
                        <span class="mb-1 block text-xs font-semibold text-slate-500">Work Group</span>
                        <select wire:model.live="workGroupFilter" class="min-h-11 w-full rounded-xl border-slate-300 text-sm">
                            <option value="">همه</option>
                            <option value="root">بدون Work Group</option>
                            @foreach($workGroups as $group)
                                <option value="{{ $group->id }}">{{ str_repeat('— ', max(0, $group->display_depth - 1)) }}{{ $group->title }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </div>
        </div>

        <div id="tasks" class="relative -mx-2 overflow-x-auto overscroll-x-contain px-2 pb-3">
            <div wire:loading.flex wire:target="moveTask,taskSearch,workGroupFilter" class="absolute inset-0 z-10 items-start justify-center bg-white/70 pt-8 text-sm font-semibold text-workspace-teal" role="status">در حال به‌روزرسانی برد...</div>
            <div class="flex min-w-max gap-4" role="list" aria-label="کانبان پروژه">
                @foreach($statuses as $status)
                    @php($columnTasks = $tasks->where('project_status_id', $status->id))
                    <section
                        class="w-[82vw] max-w-sm shrink-0 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:w-80"
                        x-data
                        @dragover.prevent
                        @drop.prevent="$wire.moveTask(parseInt($event.dataTransfer.getData('text/plain')), {{ $status->id }})"
                    >
                        <div class="mb-3 flex items-center justify-between gap-2 border-b border-slate-200 pb-3">
                            <div class="flex items-center gap-2">
                                <h3 class="font-black">{{ $status->title }}</h3>
                                @if($status->is_done)<x-ui.badge tone="success">Done</x-ui.badge>@endif
                            </div>
                            <span class="rounded-full bg-white px-2 py-1 text-xs font-bold text-slate-500">{{ $columnTasks->count() }}</span>
                        </div>

                        <div class="min-h-24 space-y-2">
                            @forelse($columnTasks as $task)
                                <article
                                    wire:key="kanban-task-{{ $task->id }}"
                                    draggable="{{ $project->status->value === 'active' ? 'true' : 'false' }}"
                                    @dragstart="$event.dataTransfer.setData('text/plain', '{{ $task->id }}')"
                                    aria-label="{{ $task->reference }}: {{ $task->title }}"
                                    class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-workspace-teal hover:shadow-md {{ $task->projectStatus?->is_done ? 'bg-emerald-50/50' : '' }}"
                                >
                                    <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block">
                                        <div class="text-xs font-bold text-slate-400">{{ $task->reference }}</div>
                                        <div class="mt-1 font-bold leading-6 text-slate-900 {{ $task->projectStatus?->is_done ? 'line-through decoration-emerald-600' : '' }}">{{ $task->title }}</div>
                                        <div class="mt-3 space-y-1 text-xs text-slate-500">
                                            <div>مسئول: {{ $task->assignee?->full_name ?? 'بدون مسئول' }}</div>
                                            <div>اولویت: {{ __('tasks::messages.priorities.'.$task->priority->value) }}</div>
                                            @if($task->due_date)<div>موعد: <x-ui.date :value="$task->due_date" /></div>@endif
                                            @if($task->workGroup)<div>Work Group: {{ $task->workGroup->title }}</div>@endif
                                        </div>
                                    </a>
                                    @if($project->status->value === 'active')
                                        <label class="mt-3 block text-xs text-slate-500">
                                            <span class="mb-1 block">انتقال وضعیت</span>
                                            <select wire:change="moveTask({{ $task->id }}, $event.target.value)" class="min-h-11 w-full rounded-lg border-slate-300 text-xs">
                                                @foreach($statuses as $targetStatus)
                                                    <option value="{{ $targetStatus->id }}" @selected($targetStatus->id === $task->project_status_id)>{{ $targetStatus->title }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-xs text-slate-400">تسکی در این ستون نیست.</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </x-ui.card>
    </section>

    @if($workGroups->isNotEmpty())
        <x-ui.card>
            <div class="mb-4">
                <h2 class="font-black">ساختار پروژه</h2>
                <p class="mt-1 text-xs text-slate-500">نمای Hierarchy بر اساس Work Group است؛ جستجو مستقل از ساختار روی همه تسک‌ها اعمال می‌شود.</p>
            </div>

            <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h3 class="font-bold">Root Tasks</h3>
                    <span class="text-xs text-slate-400">مستقیم زیر پروژه</span>
                </div>
                <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($rootTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" wire:navigate class="rounded-lg border border-slate-200 bg-white p-3 hover:bg-slate-50">
                            <div class="text-xs font-bold text-slate-400">{{ $task->reference }}</div>
                            <div class="mt-1 font-bold">{{ $task->title }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $task->projectStatus->title }}</div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">تسک Root مطابق جستجوی فعلی وجود ندارد.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-3">
                @foreach($workGroups as $group)
                    @php($directTasks = $tasksByWorkGroup->get($group->id, collect()))
                    @php($progress = $workGroupProgress[$group->id])
                    <section class="rounded-xl border border-slate-200 p-3" style="margin-right: {{ min(4, max(0, $group->display_depth - 1)) * 1.25 }}rem">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="text-xs text-slate-400">Level {{ $group->display_depth }}</div>
                                <h3 class="mt-1 font-black">{{ $group->title }}</h3>
                                @if($group->description)<p class="mt-1 text-sm leading-6 text-slate-500">{{ $group->description }}</p>@endif
                            </div>
                            <div class="text-xs font-semibold text-slate-500">
                                @if($progress['percentage'] === null)
                                    Progress: N/A
                                @else
                                    Progress: {{ $progress['done'] }}/{{ $progress['total'] }} · {{ $progress['percentage'] }}%
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                            @forelse($directTasks as $task)
                                <a href="{{ route('tasks.show', $task) }}" wire:navigate class="rounded-lg border border-slate-200 bg-slate-50 p-3 hover:bg-white">
                                    <div class="text-xs font-bold text-slate-400">{{ $task->reference }}</div>
                                    <div class="mt-1 font-bold">{{ $task->title }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $task->projectStatus->title }}</div>
                                </a>
                            @empty
                                <p class="text-xs text-slate-400">تسک مستقیم مطابق جستجوی فعلی ندارد.</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    @if($isAdmin)
        <section id="project-management" class="scroll-mt-6 space-y-3" aria-labelledby="project-management-heading">
            <div>
                <h2 id="project-management-heading" class="text-lg font-black text-slate-950">Project Management</h2>
                <p class="mt-1 text-sm text-slate-500">مدیریت Workflow و Work Group فقط برای مدیر پروژه فعال است؛ محدودیت‌های وضعیت و حداکثر پنج سطح سلسله‌مراتب همچنان از سمت دامنه enforce می‌شوند.</p>
            </div>
        <div class="grid gap-5 xl:grid-cols-2">
            <x-ui.card>
                <div class="mb-4">
                    <h2 class="font-black">Workflow پروژه</h2>
                    <p class="mt-1 text-xs text-slate-500">حداقل دو Status فعال و دقیقاً یک Done Status حفظ می‌شود.</p>
                </div>

                @if($project->status->value === 'active')
                    <form wire:submit="createStatus" class="mb-4 flex gap-2">
                        <input wire:model="newStatusTitle" maxlength="120" required placeholder="نام Status جدید" class="min-h-11 min-w-0 flex-1 rounded-xl border-slate-300 text-sm">
                        <x-ui.button type="submit">افزودن</x-ui.button>
                    </form>
                @endif

                <div class="space-y-3">
                    @foreach($statuses as $status)
                        <div wire:key="workflow-status-{{ $status->id }}" class="rounded-xl border border-slate-200 p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <input wire:model="statusTitles.{{ $status->id }}" class="min-h-11 min-w-44 flex-1 rounded-lg border-slate-300 text-sm" @disabled($project->status->value !== 'active')>
                                @if($status->is_done)<x-ui.badge tone="success">Done</x-ui.badge>@endif
                                @if($project->status->value === 'active')
                                    <button type="button" wire:click="renameStatus({{ $status->id }})" class="min-h-11 rounded-lg border px-3 py-2 text-xs font-bold">ذخیره نام</button>
                                    @unless($status->is_done)<button type="button" wire:click="setDoneStatus({{ $status->id }})" class="min-h-11 rounded-lg border px-3 py-2 text-xs font-bold">انتخاب Done</button>@endunless
                                    <button type="button" wire:click="moveStatus({{ $status->id }}, 'up')" aria-label="بالا" class="min-h-11 min-w-11 rounded-lg border px-3 py-2 text-xs">↑</button>
                                    <button type="button" wire:click="moveStatus({{ $status->id }}, 'down')" aria-label="پایین" class="min-h-11 min-w-11 rounded-lg border px-3 py-2 text-xs">↓</button>
                                    @unless($status->is_done)<button type="button" wire:click="inactivateStatus({{ $status->id }})" class="min-h-11 rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">غیرفعال</button>@endunless
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="mb-4">
                    <h2 class="font-black">مدیریت Work Group</h2>
                    <p class="mt-1 text-xs text-slate-500">اختیاری، Generic و حداکثر ۵ سطح.</p>
                </div>

                @if($project->status->value === 'active')
                    <form wire:submit="createWorkGroup" class="mb-4 space-y-2 rounded-xl border border-slate-200 p-3">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <input wire:model="newWorkGroupTitle" maxlength="255" required placeholder="نام Work Group" class="min-h-11 rounded-xl border-slate-300 text-sm">
                            <select wire:model="newWorkGroupParentId" class="min-h-11 rounded-xl border-slate-300 text-sm">
                                <option value="">ریشه پروژه</option>
                                @foreach($workGroups as $candidate)
                                    @if($candidate->display_depth < 5)
                                        <option value="{{ $candidate->id }}">{{ str_repeat('— ', max(0, $candidate->display_depth - 1)) }}{{ $candidate->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <textarea wire:model="newWorkGroupDescription" rows="2" maxlength="2000" placeholder="توضیحات اختیاری" class="min-h-11 w-full rounded-xl border-slate-300 text-sm"></textarea>
                        <x-ui.button type="submit">افزودن Work Group</x-ui.button>
                    </form>
                @endif

                <div class="space-y-2">
                    @forelse($workGroups as $group)
                        <div wire:key="work-group-{{ $group->id }}" class="rounded-xl border border-slate-200 p-3" style="margin-right: {{ min(4, max(0, $group->display_depth - 1)) * 1.25 }}rem">
                            <div class="mb-2 flex items-center gap-2 text-xs text-slate-400"><span>Level {{ $group->display_depth }}</span><span>•</span><span>#{{ $group->id }}</span></div>
                            <div class="space-y-2">
                                <input wire:model="workGroupTitles.{{ $group->id }}" class="min-h-11 w-full rounded-lg border-slate-300 text-sm" @disabled($project->status->value !== 'active')>
                                <textarea wire:model="workGroupDescriptions.{{ $group->id }}" rows="2" maxlength="2000" class="min-h-11 w-full rounded-lg border-slate-300 text-sm" @disabled($project->status->value !== 'active')></textarea>
                                <select wire:model="workGroupParents.{{ $group->id }}" class="min-h-11 w-full rounded-lg border-slate-300 text-sm" @disabled($project->status->value !== 'active')>
                                    <option value="">ریشه پروژه</option>
                                    @foreach($workGroups as $candidate)
                                        @if($candidate->id !== $group->id)
                                            <option value="{{ $candidate->id }}">{{ str_repeat('— ', max(0, $candidate->display_depth - 1)) }}{{ $candidate->title }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @if($project->status->value === 'active')
                                    <div class="flex flex-wrap gap-1">
                                        <button type="button" wire:click="saveWorkGroup({{ $group->id }})" class="min-h-11 rounded-lg border px-3 py-2 text-xs">ذخیره</button>
                                        <button type="button" wire:click="moveWorkGroup({{ $group->id }})" class="min-h-11 rounded-lg border px-3 py-2 text-xs">انتقال</button>
                                        <button type="button" wire:click="moveWorkGroupPosition({{ $group->id }}, 'up')" aria-label="بالا" class="min-h-11 min-w-11 rounded-lg border px-3 py-2 text-xs">↑</button>
                                        <button type="button" wire:click="moveWorkGroupPosition({{ $group->id }}, 'down')" aria-label="پایین" class="min-h-11 min-w-11 rounded-lg border px-3 py-2 text-xs">↓</button>
                                        <button type="button" wire:click="inactivateWorkGroup({{ $group->id }})" class="min-h-11 rounded-lg border border-rose-200 px-3 py-2 text-xs text-rose-700">غیرفعال</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">این پروژه Work Group ندارد؛ مدل Project → Task همچنان کاملاً معتبر است.</p>
                    @endforelse
                </div>

                @if($inactiveWorkGroups->isNotEmpty())
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <h3 class="mb-2 text-sm font-black">Work Groupهای غیرفعال</h3>
                        <div class="space-y-2">
                            @foreach($inactiveWorkGroups as $inactiveGroup)
                                <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                                    <div class="font-bold">{{ $inactiveGroup->title }}</div>
                                    @if($inactiveGroup->description)<div class="mt-1 text-xs leading-5">{{ $inactiveGroup->description }}</div>@endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-ui.card>
        </div>
        </section>
    @endif

    <section id="members" class="scroll-mt-6">
    <x-ui.card>
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-black">اعضای پروژه</h2>
            @if($isAdmin)<a class="text-sm font-semibold text-slate-600 hover:text-slate-950" href="{{ route('projects.edit', $project) }}" wire:navigate>مدیریت اعضا</a>@endif
        </div>
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($members as $member)
                <div class="rounded-xl border border-slate-200 p-3">
                    <div class="font-bold">{{ $member->full_name }}</div>
                    @if($isAdmin)<div class="mt-1 text-xs text-slate-500"><span dir="ltr">{{ $member->email }}</span>@if($member->mobile) · <span dir="ltr">{{ $member->mobile }}</span>@endif</div>@endif
                </div>
            @empty
                <p class="text-sm text-slate-500">عضوی ندارد.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $members->links() }}</div>
    </x-ui.card>
    </section>

    <section id="activity" class="scroll-mt-6">
    <x-ui.card>
        <h2 class="mb-4 font-black">فعالیت‌های اخیر</h2>
        <div class="space-y-3">
            @forelse($activities as $activity)
                <div class="flex flex-col gap-1 border-b border-slate-100 pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                    <div><span class="font-semibold">{{ $activity->actor?->full_name ?? 'سیستم' }}</span> <span class="text-sm text-slate-600">{{ __('tasks::messages.activity_actions.'.$activity->action) }}</span></div>
                    <time class="text-xs text-slate-500"><x-ui.date :value="$activity->created_at" datetime /></time>
                </div>
            @empty
                <p class="text-sm text-slate-500">فعالیتی ثبت نشده است.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $activities->links() }}</div>
    </x-ui.card>
    </section>
</div>
