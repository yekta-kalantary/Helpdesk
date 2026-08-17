@php
    $isOverdue = $task->due_date && $task->due_date->isBefore(today()) && ! $task->isDone();
@endphp

<div class="space-y-8 overflow-x-clip sm:space-y-10">
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

    <div class="-mt-4 flex flex-wrap items-center gap-3 border-y border-border py-4 sm:-mt-5 sm:py-5" aria-label="وضعیت تسک">
        <span dir="ltr" class="text-body-sm font-semibold text-text-muted">{{ $task->reference }}</span>
        <span class="text-body-sm font-semibold text-text-muted">وضعیت فعلی</span>
        <x-ui.badge :tone="$task->projectStatus->is_done ? 'success' : 'neutral'">{{ $task->projectStatus->title }}</x-ui.badge>
        @if(!$canCollaborate)
            <span class="text-body-sm text-text-muted">این تسک فقط خواندنی است.</span>
        @endif
    </div>

    @error('status')<x-ui.alert tone="danger">{{ $message }}</x-ui.alert>@enderror

    <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_19rem] xl:grid-cols-[minmax(0,1fr)_21rem]">
        <main class="min-w-0 space-y-6 sm:space-y-8">
            <x-ui.card title="شرح تسک" subtitle="زمینه و جزئیات مورد نیاز برای ادامه کار">
                @if($task->description)
                    <div class="break-words whitespace-pre-wrap text-body leading-8 text-text">{{ $task->description }}</div>
                @else
                    <p class="text-body-sm text-text-muted">برای این تسک توضیحی ثبت نشده است.</p>
                @endif
            </x-ui.card>

             <x-ui.card title="گفت‌وگو" subtitle="آخرین تصمیم‌ها و فایل‌های مرتبط با این تسک" wire:loading.class="ui-loading-stable" wire:target="addComment,uploads,hideComment,hideAttachment">
                <div class="space-y-4">
                    @forelse($comments as $commentItem)
                        <article class="rounded-surface border border-border bg-info-surface/30 p-4 sm:p-5" wire:key="comment-{{ $commentItem->id }}">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                  <div class="break-words font-bold text-text">{{ $commentItem->user->full_name }}</div>
                                  <div class="flex items-center gap-2 text-caption text-text-muted">
                                    <time datetime="{{ $commentItem->created_at->toIso8601String() }}"><x-ui.date :value="$commentItem->created_at" datetime /></time>
                                    @if($isAdmin && !$commentItem->hidden_at)
                                         <button type="button" wire:click="hideComment({{ $commentItem->id }})" wire:confirm="این نظر از دید مشتری مخفی شود؟" class="min-h-11 rounded-control px-2 font-semibold text-text-muted hover:text-text" aria-label="مخفی‌کردن نظر">مخفی‌کردن</button>
                                    @endif
                                </div>
                            </div>
                            @if($commentItem->hidden_at)
                                  <p class="text-body-sm text-text-muted">این نظر توسط ادمین مخفی شده است.</p>
                            @else
                                 @if($commentItem->body)<div class="break-words whitespace-pre-wrap text-body-sm leading-7 text-text">{{ $commentItem->body }}</div>@endif
                                @if($commentItem->attachments->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap gap-2" aria-label="فایل‌های نظر">
                                        @foreach($commentItem->attachments as $attachment)
                                            @if($attachment->hidden_at)
                                                  <span class="rounded-control bg-surface-muted px-3 py-2 text-caption text-text-muted">فایل مخفی‌شده: {{ $attachment->original_name }}</span>
                                            @else
                                                       <span class="inline-flex max-w-full flex-wrap items-center gap-2 rounded-control bg-surface px-3 py-2 text-caption ring-1 ring-border">
                                                           <a href="{{ route('attachments.download', $attachment) }}" class="break-all font-semibold text-text hover:underline">{{ $attachment->original_name }}</a>
                                                          @if($attachment->isPreviewable())<a href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer" class="font-semibold text-text-muted hover:text-text">پیش‌نمایش</a>@endif
                                                          @if($isAdmin)<button type="button" wire:click="hideAttachment({{ $attachment->id }})" aria-label="مخفی‌کردن فایل {{ $attachment->original_name }}" class="min-h-11 rounded-control px-1 text-text-muted hover:text-text">مخفی</button>@endif
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </article>
                    @empty
                         <p class="rounded-surface border border-dashed border-border px-4 py-5 text-body-sm text-text-muted">هنوز گفت‌وگویی شروع نشده است.</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $comments->links() }}</div>

                @if($canCollaborate)
                     <form wire:submit="addComment" class="mt-6 border-t border-border pt-5" aria-label="ثبت نظر جدید">
                        <div class="space-y-4">
                            <x-ui.textarea name="comment" label="نظر جدید" :value="$comment" wire:model="comment" />
                            <div>
                                  <label for="task-uploads" class="mb-2 block text-label font-semibold text-text">فایل‌ها</label>
                                  <input id="task-uploads" type="file" multiple wire:model="uploads" wire:loading.attr="disabled" wire:target="uploads" class="block min-h-11 w-full rounded-control border border-border bg-surface px-3 py-2 text-body-sm" />
                                  <p class="mt-1 text-caption text-text-muted" wire:loading wire:target="uploads" role="status">در حال آماده‌سازی فایل‌ها...</p>
                                @error('uploads.*')<p class="mt-2 text-xs font-medium text-danger-text">{{ $message }}</p>@enderror
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
                          <div class="flex flex-col gap-2 rounded-surface border border-border p-3 sm:flex-row sm:items-center sm:justify-between">
                             <div class="min-w-0">
                                 @if($attachment->hidden_at)
                                      <span class="text-body-sm font-semibold text-text-muted">فایل مخفی‌شده: {{ $attachment->original_name }}</span>
                                 @else
                                     <div class="flex flex-wrap items-center gap-2">
                                           <a class="break-all font-semibold text-text hover:underline" href="{{ route('attachments.download', $attachment) }}">{{ $attachment->original_name }}</a>
                                          @if($attachment->isPreviewable())<a class="text-caption font-semibold text-text-muted hover:text-text" href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer">پیش‌نمایش</a>@endif
                                     </div>
                                 @endif
                                  <div class="mt-1 text-caption text-text-muted">{{ number_format($attachment->size / 1024, 1) }} KB · {{ $attachment->mime_type }}</div>
                             </div>
                             @if($isAdmin && !$attachment->hidden_at)<x-ui.button variant="secondary" wire:click="hideAttachment({{ $attachment->id }})" wire:confirm="این فایل از دید مشتری مخفی شود؟">مخفی‌کردن</x-ui.button>@endif
                         </div>
                     @empty
                          <p class="rounded-surface border border-dashed border-border px-4 py-5 text-body-sm text-text-muted">هنوز فایل مستقلی برای این تسک ثبت نشده است.</p>
                     @endforelse
                 </div>
                 <div class="mt-4">{{ $taskAttachments->links() }}</div>
             </x-ui.card>

             <x-ui.card title="چک‌لیست تسک" :subtitle="$task->checklistItems->isNotEmpty() ? $checklistCompleted.'/'.$task->checklistItems->count().' انجام‌شده' : 'مراحل قابل پیگیری برای تکمیل تسک'" wire:loading.class="ui-loading-stable" wire:target="addSubtask,toggleSubtask,renameSubtask,removeSubtask,moveSubtask">
                @error('checklist')<x-ui.alert class="mb-4" tone="danger">{{ $message }}</x-ui.alert>@enderror
                <div class="space-y-2">
                    @forelse($task->checklistItems as $item)
                        <div class="flex flex-col gap-2 rounded-surface border border-border p-3 sm:flex-row sm:items-center" wire:key="subtask-{{ $item->id }}">
                            @if($canCollaborate)
                                <button type="button" class="h-11 w-11 shrink-0 rounded-control border p-0 text-caption" wire:click="toggleSubtask({{ $item->id }}, {{ $item->is_completed ? 'false' : 'true' }})" aria-label="تغییر وضعیت زیرتسک"><span class="flex h-6 w-6 items-center justify-center rounded-control border text-caption">{{ $item->is_completed ? '✓' : '' }}</span></button>
                                 <input type="text" wire:model="checklistEdits.{{ $item->id }}" value="{{ $checklistEdits[(string) $item->id] ?? $item->title }}" class="min-h-11 min-w-0 flex-1 rounded-control border border-border px-3 py-2 text-body-sm {{ $item->is_completed ? 'line-through text-text-muted' : '' }}" aria-label="عنوان زیرتسک" />
                                <div class="flex flex-wrap gap-1">
                                    <x-ui.button variant="secondary" wire:click="renameSubtask({{ $item->id }})">ذخیره</x-ui.button>
                                    <x-ui.button variant="secondary" wire:click="moveSubtask({{ $item->id }}, 'up')" aria-label="انتقال زیرتسک به بالا">↑</x-ui.button>
                                    <x-ui.button variant="secondary" wire:click="moveSubtask({{ $item->id }}, 'down')" aria-label="انتقال زیرتسک به پایین">↓</x-ui.button>
                                    <x-ui.button variant="secondary" wire:click="removeSubtask({{ $item->id }})" wire:confirm="این زیرتسک به‌صورت منطقی حذف شود؟">حذف</x-ui.button>
                                </div>
                            @else
                                <span class="flex min-h-11 h-6 w-6 shrink-0 items-center justify-center rounded-control border text-caption" role="img" aria-label="{{ $item->is_completed ? 'زیرتسک انجام شده است' : 'زیرتسک انجام نشده است' }}">{{ $item->is_completed ? '✓' : '' }}</span>
                                 <span @class(['text-body-sm', 'line-through text-text-muted' => $item->is_completed])>{{ $item->title }}</span>
                            @endif
                        </div>
                    @empty
                         <p class="rounded-surface border border-dashed border-border px-4 py-5 text-body-sm text-text-muted">هنوز مرحله‌ای به چک‌لیست اضافه نشده است.</p>
                    @endforelse
                </div>
                @if($canCollaborate)
                    <form wire:submit="addSubtask" class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <div class="flex-1"><x-ui.input name="checklistTitle" :value="$checklistTitle" wire:model="checklistTitle" placeholder="یک مرحله کوچک اضافه کنید" /></div>
                        <x-ui.button type="submit" icon="fa-plus">افزودن</x-ui.button>
                    </form>
                @else
                     <p class="mt-4 text-caption text-text-muted">چک‌لیست در تسک Done یا پروژه تکمیل‌شده فقط خواندنی است.</p>
                @endif
            </x-ui.card>

             <details class="group rounded-surface border border-border bg-surface">
                 <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 px-4 py-4 font-bold text-text sm:px-6 [&::-webkit-details-marker]:hidden">
                     <span>تاریخچه فعالیت</span><span class="text-caption font-medium text-text-muted group-open:hidden">نمایش</span><span class="hidden text-caption font-medium text-text-muted group-open:inline">مخفی‌کردن</span>
                </summary>
                 <div class="border-t border-border px-4 pb-4 pt-4 sm:px-6 sm:pb-6">
                    <div class="space-y-3">
                        @forelse($activities as $activity)
                             <div class="flex flex-col gap-1 border-b border-border pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                 <div><span class="font-semibold">{{ $activity->actor?->full_name ?? 'سیستم' }}</span> <span class="text-body-sm text-text-muted">{{ __('tasks::messages.activity_actions.'.$activity->action) }}</span></div>
                                 <time class="text-caption text-text-muted"><x-ui.date :value="$activity->created_at" datetime /></time>
                            </div>
                        @empty
                             <p class="text-body-sm text-text-muted">فعالیتی ثبت نشده است.</p>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $activities->links() }}</div>
                </div>
            </details>
        </main>

        <aside class="min-w-0 space-y-5 lg:sticky lg:top-5" aria-label="اطلاعات و عملیات تسک">
             <x-ui.card title="زمینه و مالکیت" subtitle="اطلاعات عملیاتی این تسک">
                 <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1" aria-label="ویژگی‌های تسک">
                     <x-ui.meta-item label="مسئول">{{ $task->assignee?->full_name ?? __('tasks::messages.assignee.none') }}</x-ui.meta-item>
                     <x-ui.meta-item label="اولویت">{{ __('tasks::messages.priorities.'.$task->priority->value) }}</x-ui.meta-item>
                      <x-ui.meta-item label="موعد" :value-class="$isOverdue ? 'text-danger-text' : 'text-text'"><span @class(['font-bold' => $isOverdue])><x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}{{ $isOverdue ? ' · عقب‌افتاده' : '' }}</span></x-ui.meta-item>
                     <x-ui.meta-item label="پروژه">{{ $task->project->name }}</x-ui.meta-item>
                     <x-ui.meta-item label="گروه کاری">{{ $task->workGroup?->title ?? 'ریشه پروژه' }}</x-ui.meta-item>
                     <x-ui.meta-item label="ایجادکننده">{{ $task->creator->full_name }}</x-ui.meta-item>
                 </dl>
             </x-ui.card>

            <x-ui.card :padding="false">
                <details open class="group">
                     <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 px-4 py-4 font-bold text-text sm:px-6 [&::-webkit-details-marker]:hidden">
                         <span>عملیات تسک</span><span class="text-caption font-medium text-text-muted group-open:hidden">نمایش</span><span class="hidden text-caption font-medium text-text-muted group-open:inline">مخفی‌کردن</span>
                    </summary>
                     <div class="border-t border-border px-4 pb-4 pt-4 sm:px-6 sm:pb-6">
                        <div class="space-y-3">
                             <p class="text-body-sm font-semibold text-text-muted">وضعیت پروژه‌ای</p>
                            <div class="grid gap-2">
                                @foreach($activeStatuses as $statusItem)
                                    <x-ui.button :variant="$statusItem->id === $task->project_status_id ? 'primary' : 'secondary'" wire:click="changeStatus({{ $statusItem->id }})" wire:loading.attr="disabled" wire:target="changeStatus" :disabled="!$canChangeStatus || $statusItem->id === $task->project_status_id">{{ $statusItem->title }}</x-ui.button>
                                @endforeach
                            </div>
                             <p class="text-caption text-text-muted" wire:loading wire:target="changeStatus" role="status">در حال تغییر وضعیت...</p>
                             @if(!$canChangeStatus)<p class="text-caption leading-5 text-text-muted">برای تغییر وضعیت، ابتدا پروژه باید بازگشایی شود.</p>@endif
                        </div>
                    </div>
                </details>
            </x-ui.card>
        </aside>
    </div>
</div>
