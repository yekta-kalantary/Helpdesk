<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$project->name">
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

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if($isAdmin)<x-ui.card><div class="text-sm text-slate-500">مشتری</div><div class="mt-2 font-black">{{ $project->client->name }}</div></x-ui.card>@endif
        <x-ui.card><div class="text-sm text-slate-500">وضعیت پروژه</div><div class="mt-2"><x-ui.badge :tone="$project->status->value === 'active' ? 'success' : 'neutral'">{{ $project->status->value === 'active' ? 'فعال' : 'تکمیل‌شده' }}</x-ui.badge></div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">اعضا</div><div class="mt-2 text-2xl font-black">{{ $members->total() }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">تسک باز</div><div class="mt-2 text-2xl font-black">{{ $openTasksCount }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">تاریخ شروع</div><div class="mt-2 font-black"><x-ui.date :value="$project->start_date" />{{ $project->start_date ? '' : '—' }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">موعد</div><div class="mt-2 font-black"><x-ui.date :value="$project->due_date" />{{ $project->due_date ? '' : '—' }}</div></x-ui.card>
    </div>

    @if($project->description)
        <x-ui.card><div class="whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ $project->description }}</div></x-ui.card>
    @endif

    <x-ui.card>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-black">کانبان پروژه</h2>
                <p class="mt-1 text-xs text-slate-500">ستون‌ها مستقیماً از Workflow همین پروژه ساخته می‌شوند.</p>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <span class="whitespace-nowrap text-slate-500">Work Group</span>
                <select wire:model.live="workGroupFilter" class="rounded-xl border-slate-300 text-sm">
                    <option value="">همه</option>
                    <option value="root">بدون Work Group</option>
                    @foreach($workGroups as $group)
                        <option value="{{ $group->id }}">{{ str_repeat('— ', max(0, $group->display_depth - 1)) }}{{ $group->title }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="-mx-2 overflow-x-auto px-2 pb-3">
            <div class="flex min-w-max gap-4" role="list" aria-label="کانبان پروژه">
                @foreach($statuses as $status)
                    @php($columnTasks = $tasks->where('project_status_id', $status->id))
                    <section
                        class="w-[82vw] max-w-sm shrink-0 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:w-80"
                        x-data
                        @dragover.prevent
                        @drop.prevent="$wire.moveTask(parseInt($event.dataTransfer.getData('text/plain')), {{ $status->id }})"
                    >
                        <div class="mb-3 flex items-center justify-between gap-2">
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
                                    class="cursor-grab rounded-xl border border-slate-200 bg-white p-3 shadow-sm active:cursor-grabbing"
                                >
                                    <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block">
                                        <div class="text-xs font-bold text-slate-400">{{ $task->reference }}</div>
                                        <div class="mt-1 font-bold leading-6 text-slate-900">{{ $task->title }}</div>
                                        <div class="mt-3 space-y-1 text-xs text-slate-500">
                                            <div>مسئول: {{ $task->assignee?->full_name ?? 'بدون مسئول' }}</div>
                                            <div>اولویت: {{ __('tasks::messages.priorities.'.$task->priority->value) }}</div>
                                            @if($task->due_date)<div>موعد: <x-ui.date :value="$task->due_date" /></div>@endif
                                            @if($task->workGroup)<div>Work Group: {{ $task->workGroup->title }}</div>@endif
                                        </div>
                                    </a>
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

    @if($isAdmin)
        <div class="grid gap-5 xl:grid-cols-2">
            <x-ui.card>
                <div class="mb-4">
                    <h2 class="font-black">Workflow پروژه</h2>
                    <p class="mt-1 text-xs text-slate-500">حداقل دو Status فعال و دقیقاً یک Done Status حفظ می‌شود.</p>
                </div>

                @if($project->status->value === 'active')
                    <form wire:submit="createStatus" class="mb-4 flex gap-2">
                        <input wire:model="newStatusTitle" maxlength="120" required placeholder="نام Status جدید" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm">
                        <x-ui.button type="submit">افزودن</x-ui.button>
                    </form>
                @endif

                <div class="space-y-3">
                    @foreach($statuses as $status)
                        <div wire:key="workflow-status-{{ $status->id }}" class="rounded-xl border border-slate-200 p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <input wire:model="statusTitles.{{ $status->id }}" class="min-w-44 flex-1 rounded-lg border-slate-300 text-sm" @disabled($project->status->value !== 'active')>
                                @if($status->is_done)<x-ui.badge tone="success">Done</x-ui.badge>@endif
                                @if($project->status->value === 'active')
                                    <button type="button" wire:click="renameStatus({{ $status->id }})" class="rounded-lg border px-2 py-1 text-xs font-bold">ذخیره نام</button>
                                    @unless($status->is_done)<button type="button" wire:click="setDoneStatus({{ $status->id }})" class="rounded-lg border px-2 py-1 text-xs font-bold">انتخاب Done</button>@endunless
                                    <button type="button" wire:click="moveStatus({{ $status->id }}, 'up')" aria-label="بالا" class="rounded-lg border px-2 py-1 text-xs">↑</button>
                                    <button type="button" wire:click="moveStatus({{ $status->id }}, 'down')" aria-label="پایین" class="rounded-lg border px-2 py-1 text-xs">↓</button>
                                    @unless($status->is_done)<button type="button" wire:click="inactivateStatus({{ $status->id }})" class="rounded-lg border border-rose-200 px-2 py-1 text-xs font-bold text-rose-700">غیرفعال</button>@endunless
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="mb-4">
                    <h2 class="font-black">ساختار Work Group</h2>
                    <p class="mt-1 text-xs text-slate-500">اختیاری، Generic و حداکثر ۵ سطح.</p>
                </div>

                @if($project->status->value === 'active')
                    <form wire:submit="createWorkGroup" class="mb-4 grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                        <input wire:model="newWorkGroupTitle" maxlength="255" required placeholder="نام Work Group" class="rounded-xl border-slate-300 text-sm">
                        <select wire:model="newWorkGroupParentId" class="rounded-xl border-slate-300 text-sm">
                            <option value="">ریشه پروژه</option>
                            @foreach($workGroups as $candidate)
                                <option value="{{ $candidate->id }}">{{ str_repeat('— ', max(0, $candidate->display_depth - 1)) }}{{ $candidate->title }}</option>
                            @endforeach
                        </select>
                        <x-ui.button type="submit">افزودن</x-ui.button>
                    </form>
                @endif

                <div class="space-y-2">
                    @forelse($workGroups as $group)
                        <div wire:key="work-group-{{ $group->id }}" class="rounded-xl border border-slate-200 p-3" style="margin-right: {{ min(4, max(0, $group->display_depth - 1)) * 1.25 }}rem">
                            <div class="mb-2 flex items-center gap-2 text-xs text-slate-400"><span>Level {{ $group->display_depth }}</span><span>•</span><span>#{{ $group->id }}</span></div>
                            <div class="grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                                <input wire:model="workGroupTitles.{{ $group->id }}" class="rounded-lg border-slate-300 text-sm" @disabled($project->status->value !== 'active')>
                                <select wire:model="workGroupParents.{{ $group->id }}" class="rounded-lg border-slate-300 text-sm" @disabled($project->status->value !== 'active')>
                                    <option value="">ریشه پروژه</option>
                                    @foreach($workGroups as $candidate)
                                        @if($candidate->id !== $group->id)
                                            <option value="{{ $candidate->id }}">{{ str_repeat('— ', max(0, $candidate->display_depth - 1)) }}{{ $candidate->title }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @if($project->status->value === 'active')
                                    <div class="flex flex-wrap gap-1">
                                        <button type="button" wire:click="renameWorkGroup({{ $group->id }})" class="rounded-lg border px-2 py-1 text-xs">ذخیره</button>
                                        <button type="button" wire:click="moveWorkGroup({{ $group->id }})" class="rounded-lg border px-2 py-1 text-xs">انتقال</button>
                                        <button type="button" wire:click="moveWorkGroupPosition({{ $group->id }}, 'up')" class="rounded-lg border px-2 py-1 text-xs">↑</button>
                                        <button type="button" wire:click="moveWorkGroupPosition({{ $group->id }}, 'down')" class="rounded-lg border px-2 py-1 text-xs">↓</button>
                                        <button type="button" wire:click="inactivateWorkGroup({{ $group->id }})" class="rounded-lg border border-rose-200 px-2 py-1 text-xs text-rose-700">غیرفعال</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">این پروژه Work Group ندارد؛ مدل Project → Task همچنان کاملاً معتبر است.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    @else
        <x-ui.card>
            <h2 class="mb-4 font-black">ساختار Work Group</h2>
            <div class="space-y-2">
                @forelse($workGroups as $group)
                    <div class="rounded-xl border border-slate-200 p-3" style="margin-right: {{ min(4, max(0, $group->display_depth - 1)) * 1.25 }}rem">
                        <span class="text-xs text-slate-400">Level {{ $group->display_depth }}</span>
                        <div class="mt-1 font-bold">{{ $group->title }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">این پروژه Work Group ندارد.</p>
                @endforelse
            </div>
        </x-ui.card>
    @endif

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
</div>
