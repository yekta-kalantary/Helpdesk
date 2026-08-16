<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$task->reference.' · '.$task->title">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('projects.show', $task->project)" icon="fa-diagram-project" wire:navigate>{{ $task->project->name }}</x-ui.button>
            @if($canEditTask)
                <x-ui.button :href="route('tasks.edit', $task)" icon="fa-pen" wire:navigate>ویرایش تسک</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @error('status')<x-ui.alert tone="danger">{{ $message }}</x-ui.alert>@enderror

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <x-ui.card><div class="text-sm text-slate-500">وضعیت</div><div class="mt-2"><x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge></div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">گروه کاری</div><div class="mt-2 font-black">{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">اولویت</div><div class="mt-2 font-black">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">مسئول</div><div class="mt-2 font-black">{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">موعد</div><div @class(['mt-2 font-black', 'text-red-600' => $task->due_date && $task->due_date->isBefore(today()) && !$task->isDone()])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">ایجادکننده</div><div class="mt-2 font-black">{{ $task->creator->full_name }}</div></x-ui.card>
    </div>

    @if($task->description)
        <x-ui.card><div class="whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ $task->description }}</div></x-ui.card>
    @endif

    <x-ui.card>
        <div class="flex flex-wrap items-center gap-2">
            <span class="ml-2 text-sm font-semibold text-slate-600">وضعیت پروژه‌ای:</span>
            @foreach($activeStatuses as $statusItem)
                <x-ui.button
                    size="sm"
                    :variant="$statusItem->id === $task->project_status_id ? 'primary' : 'secondary'"
                    wire:click="changeStatus({{ $statusItem->id }})"
                    wire:loading.attr="disabled"
                    wire:target="changeStatus"
                    :disabled="!$canChangeStatus || $statusItem->id === $task->project_status_id"
                >{{ $statusItem->title }}</x-ui.button>
            @endforeach
        </div>
        @if(!$canChangeStatus)
            <p class="mt-3 text-xs text-slate-500">برای تغییر وضعیت، ابتدا پروژه باید بازگشایی شود.</p>
        @endif
    </x-ui.card>

    <x-ui.card>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-black">چک‌لیست تسک</h2>
            <span class="text-sm text-slate-500">{{ $checklistCompleted }}/{{ $task->checklistItems->count() }} انجام‌شده</span>
        </div>

        <div class="space-y-2">
            @forelse($task->checklistItems as $item)
                <div class="flex flex-col gap-2 rounded-xl border border-slate-200 p-3 sm:flex-row sm:items-center" wire:key="subtask-{{ $item->id }}">
                    <button type="button" class="h-6 w-6 shrink-0 rounded border text-xs" wire:click="toggleSubtask({{ $item->id }}, {{ $item->is_completed ? 'false' : 'true' }})" @disabled(!$canCollaborate) aria-label="تغییر وضعیت زیرتسک">
                        {{ $item->is_completed ? '✓' : '' }}
                    </button>
                    @if($canCollaborate)
                        <input type="text" wire:model="checklistEdits.{{ $item->id }}" class="min-w-0 flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm {{ $item->is_completed ? 'line-through text-slate-400' : '' }}" />
                        <div class="flex gap-1">
                            <x-ui.button size="sm" variant="secondary" wire:click="renameSubtask({{ $item->id }})">ذخیره</x-ui.button>
                            <x-ui.button size="sm" variant="secondary" wire:click="moveSubtask({{ $item->id }}, 'up')">↑</x-ui.button>
                            <x-ui.button size="sm" variant="secondary" wire:click="moveSubtask({{ $item->id }}, 'down')">↓</x-ui.button>
                            <x-ui.button size="sm" variant="secondary" wire:click="removeSubtask({{ $item->id }})" wire:confirm="این زیرتسک به‌صورت منطقی حذف شود؟">حذف</x-ui.button>
                        </div>
                    @else
                        <span @class(['text-sm', 'line-through text-slate-400' => $item->is_completed])>{{ $item->title }}</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">هنوز زیرتسکی ثبت نشده است.</p>
            @endforelse
        </div>

        @if($canCollaborate)
            <form wire:submit="addSubtask" class="mt-4 flex flex-col gap-2 sm:flex-row">
                <div class="flex-1">
                    <x-ui.input name="checklistTitle" :value="$checklistTitle" wire:model="checklistTitle" placeholder="یک مرحله کوچک اضافه کنید" />
                </div>
                <x-ui.button type="submit" icon="fa-plus">افزودن</x-ui.button>
            </form>
        @else
            <p class="mt-4 text-xs text-slate-500">چک‌لیست در تسک Done یا پروژه تکمیل‌شده فقط خواندنی است.</p>
        @endif
    </x-ui.card>

    @if($taskAttachments->isNotEmpty())
        <x-ui.card>
            <h2 class="mb-4 font-black">فایل‌های تسک</h2>
            <div class="space-y-2">
                @foreach($taskAttachments as $attachment)
                    <div class="flex flex-col gap-2 rounded-xl border border-slate-200 p-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            @if($attachment->hidden_at)
                                <span class="text-sm font-semibold text-slate-500">فایل مخفی‌شده: {{ $attachment->original_name }}</span>
                            @else
                                <div class="flex flex-wrap items-center gap-2">
                                    <a class="font-semibold text-slate-900 hover:underline" href="{{ route('attachments.download', $attachment) }}">{{ $attachment->original_name }}</a>
                                    @if($attachment->isPreviewable())<a class="text-xs font-semibold text-slate-600 hover:text-slate-950" href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer">پیش‌نمایش</a>@endif
                                </div>
                            @endif
                            <div class="mt-1 text-xs text-slate-500">{{ number_format($attachment->size / 1024, 1) }} KB · {{ $attachment->mime_type }}</div>
                        </div>
                        @if($isAdmin && !$attachment->hidden_at)<x-ui.button size="sm" variant="secondary" wire:click="hideAttachment({{ $attachment->id }})" wire:confirm="این فایل از دید مشتری مخفی شود؟">مخفی‌کردن</x-ui.button>@endif
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $taskAttachments->links() }}</div>
        </x-ui.card>
    @endif

    <x-ui.card>
        <h2 class="mb-4 font-black">گفت‌وگوی تسک</h2>
        <div class="space-y-4">
            @forelse($comments as $commentItem)
                <article class="rounded-2xl border border-slate-200 p-4">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div class="font-bold">{{ $commentItem->user->full_name }}</div>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <time><x-ui.date :value="$commentItem->created_at" datetime /></time>
                            @if($isAdmin && !$commentItem->hidden_at)<button type="button" wire:click="hideComment({{ $commentItem->id }})" wire:confirm="این نظر از دید مشتری مخفی شود؟" class="font-semibold text-slate-600 hover:text-slate-950">مخفی‌کردن</button>@endif
                        </div>
                    </div>
                    @if($commentItem->hidden_at)
                        <p class="text-sm text-slate-500">این نظر توسط ادمین مخفی شده است.</p>
                    @else
                        @if($commentItem->body)<div class="whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ $commentItem->body }}</div>@endif
                        @if($commentItem->attachments->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($commentItem->attachments as $attachment)
                                    @if($attachment->hidden_at)
                                        <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-500">فایل مخفی‌شده: {{ $attachment->original_name }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs">
                                            <a href="{{ route('attachments.download', $attachment) }}" class="font-semibold hover:underline">{{ $attachment->original_name }}</a>
                                            @if($attachment->isPreviewable())<a href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer" class="font-semibold text-slate-600 hover:text-slate-950">پیش‌نمایش</a>@endif
                                            @if($isAdmin)<button type="button" wire:click="hideAttachment({{ $attachment->id }})" class="text-slate-500 hover:text-slate-950">مخفی</button>@endif
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @endif
                </article>
            @empty
                <p class="text-sm text-slate-500">هنوز نظری ثبت نشده است.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $comments->links() }}</div>

        @if($canCollaborate)
            <form wire:submit="addComment" class="mt-6 border-t border-slate-100 pt-5">
                <div class="space-y-4">
                    <x-ui.textarea name="comment" label="نظر جدید" :value="$comment" wire:model="comment" />
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">فایل‌ها</label>
                        <input type="file" multiple wire:model="uploads" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" />
                        @error('uploads.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <x-ui.button type="submit" icon="fa-paper-plane" wire:loading.attr="disabled" wire:target="addComment,uploads">ثبت نظر</x-ui.button>
                </div>
            </form>
        @else
            <x-ui.alert class="mt-6" tone="neutral">این تسک یا پروژه بسته است و همکاری جدید پذیرفته نمی‌شود.</x-ui.alert>
        @endif
    </x-ui.card>

    <x-ui.card>
        <h2 class="mb-4 font-black">تاریخچه فعالیت</h2>
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
