@php
    $isOverdue = $task->due_date && $task->due_date->isBefore(today()) && ! $task->isDone();
@endphp

<div class="space-y-5 sm:space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header
        :title="$task->title"
        :subtitle="$task->reference"
        :breadcrumbs="[
            ['label' => 'پروژه‌ها', 'href' => route('projects.index')],
            ['label' => $task->project->name, 'href' => route('projects.show', $task->project)],
            ['label' => $task->reference, 'href' => null],
        ]"
    >
        <x-slot:actions>
            @if($canEditTask)
                <x-ui.button :href="route('tasks.edit', $task)" icon="fa-pen" wire:navigate>ویرایش تسک</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="-mt-3 mb-5 flex flex-wrap items-center gap-3 sm:mb-6" aria-label="وضعیت تسک">
        <span class="text-sm font-semibold text-workspace-muted">وضعیت فعلی</span>
        <x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge>
        @if(!$canCollaborate)
            <span class="text-sm text-workspace-muted">این تسک فقط خواندنی است.</span>
        @endif
    </div>

    @error('status')<x-ui.alert tone="danger">{{ $message }}</x-ui.alert>@enderror

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_18rem] xl:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="min-w-0 space-y-5 sm:space-y-6">
            <x-ui.card title="شرح تسک" subtitle="زمینه و جزئیات مورد نیاز برای ادامه کار">
                @if($task->description)
                    <div class="whitespace-pre-wrap text-sm leading-7 text-workspace-text">{{ $task->description }}</div>
                @else
                    <p class="text-sm text-workspace-muted">برای این تسک توضیحی ثبت نشده است.</p>
                @endif
            </x-ui.card>

             <x-ui.card title="گفت‌وگو" subtitle="آخرین تصمیم‌ها و فایل‌های مرتبط با این تسک" wire:loading.class="ui-loading-stable" wire:target="addComment,uploads,hideComment,hideAttachment">
                <div class="space-y-4">
                    @forelse($comments as $commentItem)
                        <article class="rounded-workspace border border-workspace-divider bg-workspace-info-surface/30 p-4" wire:key="comment-{{ $commentItem->id }}">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                 <div class="font-bold text-workspace-text">{{ $commentItem->user->full_name }}</div>
                                 <div class="flex items-center gap-2 text-xs text-workspace-muted">
                                    <time datetime="{{ $commentItem->created_at->toIso8601String() }}"><x-ui.date :value="$commentItem->created_at" datetime /></time>
                                    @if($isAdmin && !$commentItem->hidden_at)
                                         <button type="button" wire:click="hideComment({{ $commentItem->id }})" wire:confirm="این نظر از دید مشتری مخفی شود؟" class="min-h-11 rounded-md px-2 font-semibold text-workspace-muted hover:text-workspace-text" aria-label="مخفی‌کردن نظر">مخفی‌کردن</button>
                                    @endif
                                </div>
                            </div>
                            @if($commentItem->hidden_at)
                                 <p class="text-sm text-workspace-muted">این نظر توسط ادمین مخفی شده است.</p>
                            @else
                                @if($commentItem->body)<div class="whitespace-pre-wrap text-sm leading-7 text-workspace-text">{{ $commentItem->body }}</div>@endif
                                @if($commentItem->attachments->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap gap-2" aria-label="فایل‌های نظر">
                                        @foreach($commentItem->attachments as $attachment)
                                            @if($attachment->hidden_at)
                                                 <span class="rounded-lg bg-workspace-neutral-surface px-3 py-2 text-xs text-workspace-muted">فایل مخفی‌شده: {{ $attachment->original_name }}</span>
                                            @else
                                                     <span class="inline-flex items-center gap-2 rounded-lg bg-workspace-surface px-3 py-2 text-xs ring-1 ring-workspace-divider">
                                                         <a href="{{ route('attachments.download', $attachment) }}" class="font-semibold text-workspace-text hover:underline">{{ $attachment->original_name }}</a>
                                                         @if($attachment->isPreviewable())<a href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer" class="font-semibold text-workspace-muted hover:text-workspace-text">پیش‌نمایش</a>@endif
                                                         @if($isAdmin)<button type="button" wire:click="hideAttachment({{ $attachment->id }})" aria-label="مخفی‌کردن فایل {{ $attachment->original_name }}" class="min-h-11 rounded-md px-1 text-workspace-muted hover:text-workspace-text">مخفی</button>@endif
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </article>
                    @empty
                         <p class="rounded-workspace border border-dashed border-workspace-divider px-4 py-5 text-sm text-workspace-muted">هنوز گفت‌وگویی شروع نشده است.</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $comments->links() }}</div>

                @if($canCollaborate)
                     <form wire:submit="addComment" class="mt-6 border-t border-workspace-divider pt-5" aria-label="ثبت نظر جدید">
                        <div class="space-y-4">
                            <x-ui.textarea name="comment" label="نظر جدید" :value="$comment" wire:model="comment" />
                            <div>
                                 <label for="task-uploads" class="mb-2 block text-sm font-semibold text-workspace-text">فایل‌ها</label>
                                 <input id="task-uploads" type="file" multiple wire:model="uploads" wire:loading.attr="disabled" wire:target="uploads" class="block min-h-11 w-full rounded-xl border border-workspace-divider bg-workspace-surface px-3 py-2 text-sm" />
                                 <p class="mt-1 text-xs text-workspace-muted" wire:loading wire:target="uploads" role="status">در حال آماده‌سازی فایل‌ها...</p>
                                @error('uploads.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                             <x-ui.button type="submit" icon="fa-paper-plane" wire:loading.attr="disabled" wire:target="addComment,uploads" class="min-h-11">
                                <span wire:loading.remove wire:target="addComment">ثبت نظر</span>
                                <span wire:loading wire:target="addComment" role="status">در حال ثبت نظر...</span>
                            </x-ui.button>
                        </div>
                    </form>
                @else
                    <x-ui.alert class="mt-6" tone="neutral">این تسک یا پروژه بسته است و همکاری جدید پذیرفته نمی‌شود.</x-ui.alert>
                 @endif
             </x-ui.card>

             <x-ui.card title="فایل‌های تسک" subtitle="فایل‌های مستقل از گفت‌وگو" wire:loading.class="ui-loading-stable" wire:target="hideAttachment">
                 <div class="space-y-2">
                     @forelse($taskAttachments as $attachment)
                         <div class="flex flex-col gap-2 rounded-workspace border border-workspace-divider p-3 sm:flex-row sm:items-center sm:justify-between">
                             <div class="min-w-0">
                                 @if($attachment->hidden_at)
                                     <span class="text-sm font-semibold text-workspace-muted">فایل مخفی‌شده: {{ $attachment->original_name }}</span>
                                 @else
                                     <div class="flex flex-wrap items-center gap-2">
                                         <a class="font-semibold text-workspace-text hover:underline" href="{{ route('attachments.download', $attachment) }}">{{ $attachment->original_name }}</a>
                                         @if($attachment->isPreviewable())<a class="text-xs font-semibold text-workspace-muted hover:text-workspace-text" href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer">پیش‌نمایش</a>@endif
                                     </div>
                                 @endif
                                 <div class="mt-1 text-xs text-workspace-muted">{{ number_format($attachment->size / 1024, 1) }} KB · {{ $attachment->mime_type }}</div>
                             </div>
                             @if($isAdmin && !$attachment->hidden_at)<x-ui.button variant="secondary" wire:click="hideAttachment({{ $attachment->id }})" wire:confirm="این فایل از دید مشتری مخفی شود؟">مخفی‌کردن</x-ui.button>@endif
                         </div>
                     @empty
                         <p class="rounded-workspace border border-dashed border-workspace-divider px-4 py-5 text-sm text-workspace-muted">هنوز فایل مستقلی برای این تسک ثبت نشده است.</p>
                     @endforelse
                 </div>
                 <div class="mt-4">{{ $taskAttachments->links() }}</div>
             </x-ui.card>

             <x-ui.card title="چک‌لیست تسک" :subtitle="$task->checklistItems->isNotEmpty() ? $checklistCompleted.'/'.$task->checklistItems->count().' انجام‌شده' : 'مراحل قابل پیگیری برای تکمیل تسک'" wire:loading.class="ui-loading-stable" wire:target="addSubtask,toggleSubtask,renameSubtask,removeSubtask,moveSubtask">
                @error('checklist')<x-ui.alert class="mb-4" tone="danger">{{ $message }}</x-ui.alert>@enderror
                <div class="space-y-2">
                    @forelse($task->checklistItems as $item)
                        <div class="flex flex-col gap-2 rounded-workspace border border-workspace-divider p-3 sm:flex-row sm:items-center" wire:key="subtask-{{ $item->id }}">
                            @if($canCollaborate)
                                <button type="button" class="h-11 w-11 shrink-0 rounded border p-0 text-xs" wire:click="toggleSubtask({{ $item->id }}, {{ $item->is_completed ? 'false' : 'true' }})" aria-label="تغییر وضعیت زیرتسک"><span class="flex h-6 w-6 items-center justify-center rounded border text-xs">{{ $item->is_completed ? '✓' : '' }}</span></button>
                                <input type="text" wire:model="checklistEdits.{{ $item->id }}" class="min-h-11 min-w-0 flex-1 rounded-lg border border-workspace-divider px-3 py-2 text-sm {{ $item->is_completed ? 'line-through text-workspace-muted' : '' }}" aria-label="عنوان زیرتسک" />
                                <div class="flex flex-wrap gap-1">
                                    <x-ui.button variant="secondary" wire:click="renameSubtask({{ $item->id }})">ذخیره</x-ui.button>
                                    <x-ui.button variant="secondary" wire:click="moveSubtask({{ $item->id }}, 'up')" aria-label="انتقال زیرتسک به بالا">↑</x-ui.button>
                                    <x-ui.button variant="secondary" wire:click="moveSubtask({{ $item->id }}, 'down')" aria-label="انتقال زیرتسک به پایین">↓</x-ui.button>
                                    <x-ui.button variant="secondary" wire:click="removeSubtask({{ $item->id }})" wire:confirm="این زیرتسک به‌صورت منطقی حذف شود؟">حذف</x-ui.button>
                                </div>
                            @else
                                <span class="flex min-h-11 h-6 w-6 shrink-0 items-center justify-center rounded border text-xs" role="img" aria-label="{{ $item->is_completed ? 'زیرتسک انجام شده است' : 'زیرتسک انجام نشده است' }}">{{ $item->is_completed ? '✓' : '' }}</span>
                                <span @class(['text-sm', 'line-through text-workspace-muted' => $item->is_completed])>{{ $item->title }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="rounded-workspace border border-dashed border-workspace-divider px-4 py-5 text-sm text-workspace-muted">هنوز مرحله‌ای به چک‌لیست اضافه نشده است.</p>
                    @endforelse
                </div>
                @if($canCollaborate)
                    <form wire:submit="addSubtask" class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <div class="flex-1"><x-ui.input name="checklistTitle" :value="$checklistTitle" wire:model="checklistTitle" placeholder="یک مرحله کوچک اضافه کنید" /></div>
                        <x-ui.button type="submit" icon="fa-plus">افزودن</x-ui.button>
                    </form>
                @else
                    <p class="mt-4 text-xs text-workspace-muted">چک‌لیست در تسک Done یا پروژه تکمیل‌شده فقط خواندنی است.</p>
                @endif
            </x-ui.card>

            <details class="group rounded-workspace border border-workspace-divider bg-workspace-surface">
                <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 px-4 py-4 font-bold text-workspace-text sm:px-6 [&::-webkit-details-marker]:hidden">
                    <span>تاریخچه فعالیت</span><span class="text-xs font-medium text-workspace-muted group-open:hidden">نمایش</span><span class="hidden text-xs font-medium text-workspace-muted group-open:inline">مخفی‌کردن</span>
                </summary>
                <div class="border-t border-workspace-divider px-4 pb-4 pt-4 sm:px-6 sm:pb-6">
                    <div class="space-y-3">
                        @forelse($activities as $activity)
                            <div class="flex flex-col gap-1 border-b border-workspace-divider pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div><span class="font-semibold">{{ $activity->actor?->full_name ?? 'سیستم' }}</span> <span class="text-sm text-workspace-muted">{{ __('tasks::messages.activity_actions.'.$activity->action) }}</span></div>
                                <time class="text-xs text-workspace-muted"><x-ui.date :value="$activity->created_at" datetime /></time>
                            </div>
                        @empty
                            <p class="text-sm text-workspace-muted">فعالیتی ثبت نشده است.</p>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $activities->links() }}</div>
                </div>
            </details>
        </div>

        <aside class="min-w-0 space-y-5 lg:sticky lg:top-5">
             <x-ui.card title="زمینه و مالکیت" subtitle="اطلاعات عملیاتی این تسک">
                 <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1" aria-label="ویژگی‌های تسک">
                     <x-ui.meta-item label="مسئول">{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</x-ui.meta-item>
                     <x-ui.meta-item label="اولویت">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</x-ui.meta-item>
                     <x-ui.meta-item label="موعد" :value-class="$isOverdue ? 'text-workspace-danger' : 'text-workspace-text'"><span @class(['font-bold' => $isOverdue])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}{{ $isOverdue ? ' · عقب‌افتاده' : '' }}</span></x-ui.meta-item>
                     <x-ui.meta-item label="پروژه">{{ $task->project->name }}</x-ui.meta-item>
                     <x-ui.meta-item label="گروه کاری">{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</x-ui.meta-item>
                     <x-ui.meta-item label="ایجادکننده">{{ $task->creator->full_name }}</x-ui.meta-item>
                 </dl>
             </x-ui.card>

            <x-ui.card :padding="false">
                <details open class="group">
                    <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 px-4 py-4 font-bold text-workspace-text sm:px-6 [&::-webkit-details-marker]:hidden">
                        <span>عملیات تسک</span><span class="text-xs font-medium text-workspace-muted group-open:hidden">نمایش</span><span class="hidden text-xs font-medium text-workspace-muted group-open:inline">مخفی‌کردن</span>
                    </summary>
                    <div class="border-t border-workspace-divider px-4 pb-4 pt-4 sm:px-6 sm:pb-6">
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-workspace-muted">وضعیت پروژه‌ای</p>
                            <div class="grid gap-2">
                                @foreach($activeStatuses as $statusItem)
                                    <x-ui.button :variant="$statusItem->id === $task->project_status_id ? 'primary' : 'secondary'" wire:click="changeStatus({{ $statusItem->id }})" wire:loading.attr="disabled" wire:target="changeStatus" :disabled="!$canChangeStatus || $statusItem->id === $task->project_status_id">{{ $statusItem->title }}</x-ui.button>
                                @endforeach
                            </div>
                            <p class="text-xs text-workspace-muted" wire:loading wire:target="changeStatus" role="status">در حال تغییر وضعیت...</p>
                            @if(!$canChangeStatus)<p class="text-xs leading-5 text-workspace-muted">برای تغییر وضعیت، ابتدا پروژه باید بازگشایی شود.</p>@endif
                        </div>
                    </div>
                </details>
            </x-ui.card>
        </aside>
    </div>
</div>
