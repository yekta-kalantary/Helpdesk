<div class="space-y-8 overflow-x-clip">
    <x-ui.page-header :title="__('app.dashboard')">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-body-sm text-text-muted">خلاصه وضعیت پروژه‌ها و تسک‌های قابل دسترسی شما</p>
            <a href="{{ route('notifications.index') }}" wire:navigate class="inline-flex min-h-11 items-center gap-2 self-start rounded-button border border-border bg-surface px-3 text-body-sm font-semibold text-text-muted transition hover:border-primary hover:text-primary sm:self-auto">
                <i class="fa-light fa-bell" aria-hidden="true"></i>
                اعلان‌ها
            </a>
        </div>
    </x-ui.page-header>

    <section class="rounded-surface border border-accent/30 bg-surface p-5 sm:p-7" aria-labelledby="dashboard-focus-heading">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-label font-semibold text-accent-text">تمرکز امروز</p>
                <h2 id="dashboard-focus-heading" class="mt-2 text-heading-lg font-semibold text-text sm:text-heading-xl">
                    @if($isAdmin) صف کارهای بدون مسئول را بررسی کنید @else کارهای واگذار شده به خودتان را پیش ببرید @endif
                </h2>
                <p class="mt-2 text-body-sm leading-7 text-text-muted">
                    @if($isAdmin)
                        {{ number_format($unassignedOpenTaskCount) }} تسک باز هنوز مسئول ندارد و می‌تواند نقطه شروع برنامه امروز باشد.
                    @else
                        {{ number_format($assignedToMeCount) }} تسک باز اکنون با شماست؛ از آخرین بروزرسانی‌ها شروع کنید.
                    @endif
                </p>
            </div>
            @if($isAdmin)
                    <a href="{{ route('tasks.index', ['assignee' => 'unassigned']) }}" wire:navigate class="inline-flex min-h-11 items-center justify-center gap-2 rounded-button bg-accent px-4 text-body-sm font-bold text-surface transition hover:brightness-95">
                    <i class="fa-light fa-inbox" aria-hidden="true"></i>
                    مشاهده صف بدون مسئول
                </a>
            @else
                    <a href="{{ route('tasks.index', ['assignee' => auth()->id()]) }}" wire:navigate class="inline-flex min-h-11 items-center justify-center gap-2 rounded-button bg-accent px-4 text-body-sm font-bold text-surface transition hover:brightness-95">
                    <i class="fa-light fa-user-check" aria-hidden="true"></i>
                    مشاهده کارهای من
                </a>
            @endif
        </div>
    </section>

    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 border-y border-border py-4" aria-label="خلاصه آمار">
        @if($isAdmin)
            <a href="{{ route('clients.index', ['status' => 'active']) }}" wire:navigate class="group inline-flex min-h-11 items-center gap-2 rounded-control px-2 py-1 transition hover:bg-surface-muted">
                <span class="text-body-sm text-text-muted">مشتریان فعال</span>
                <strong class="text-heading-lg text-text">{{ number_format($activeClientCount) }}</strong>
            </a>
        @endif

        <a href="{{ route('projects.index', ['status' => 'active']) }}" wire:navigate class="group inline-flex min-h-11 items-center gap-2 rounded-control px-2 py-1 transition hover:bg-surface-muted">
            <span class="text-body-sm text-text-muted">{{ $isAdmin ? 'پروژه‌های فعال' : 'پروژه‌های فعال من' }}</span>
            <strong class="text-heading-lg text-text">{{ number_format($activeProjectCount) }}</strong>
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="group inline-flex min-h-11 items-center gap-2 rounded-control px-2 py-1 transition hover:bg-surface-muted">
            <span class="text-body-sm text-text-muted">تسک‌های باز</span>
            <strong class="text-heading-lg text-text">{{ number_format($openTaskCount) }}</strong>
        </a>

        @if($isAdmin)
            <a href="{{ route('tasks.index', ['assignee' => 'unassigned']) }}" wire:navigate class="group inline-flex min-h-11 items-center gap-2 rounded-control px-2 py-1 transition hover:bg-surface-muted">
                <span class="text-body-sm text-text-muted">صف بدون مسئول</span>
                <strong class="text-heading-lg text-info-text">{{ number_format($unassignedOpenTaskCount) }}</strong>
            </a>
        @else
            <a href="{{ route('tasks.index', ['assignee' => auth()->id()]) }}" wire:navigate class="group inline-flex min-h-11 items-center gap-2 rounded-control px-2 py-1 transition hover:bg-surface-muted">
                <span class="text-body-sm text-text-muted">واگذار شده به من</span>
                <strong class="text-heading-lg text-info-text">{{ number_format($assignedToMeCount) }}</strong>
            </a>
        @endif

        <a href="{{ route('tasks.index', ['overdue' => 1]) }}" wire:navigate class="group inline-flex min-h-11 items-center gap-2 rounded-control px-2 py-1 transition hover:bg-danger-surface">
            <span class="text-body-sm text-text-muted">عقب‌افتاده</span>
            <strong class="text-heading-lg text-danger-text">{{ number_format($overdueCount) }}</strong>
        </a>
    </div>

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.65fr)]">
        <div class="space-y-8">
            <section aria-labelledby="dashboard-tasks-heading">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 id="dashboard-tasks-heading" class="text-heading-lg font-semibold text-text">تسک‌های اولویت‌دار</h2>
                    <a href="{{ route('tasks.index') }}" wire:navigate class="inline-flex min-h-11 shrink-0 items-center rounded-control px-2 text-body-sm font-semibold text-text-muted transition hover:bg-surface-muted hover:text-text">مشاهده همه</a>
                </div>
                <div class="divide-y divide-border overflow-hidden rounded-surface border border-border bg-surface">
                    @forelse($recentTasks as $task)
                        @php($isOverdue = $task->due_date && $task->due_date->isBefore(today()) && ! $task->isDone())
                        <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block min-h-11 p-4 transition hover:bg-surface-muted sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <span dir="ltr" class="text-caption font-bold text-info-text">{{ $task->reference }}</span>
                                        <span class="text-text-muted" aria-hidden="true">·</span>
                                        <h3 class="break-words font-bold text-text">{{ $task->title }}</h3>
                                    </div>
                                    <p class="mt-1 text-body-sm text-text-muted">{{ $task->project->name }}</p>
                                </div>
                                <x-ui.badge :tone="$task->isDone() ? 'success' : 'info'">{{ $task->projectStatus->title }}</x-ui.badge>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-caption text-text-muted">
                                <span>اولویت: {{ __('tasks::messages.priorities.'.$task->priority->value) }}</span>
                                <span>{{ $task->assignee?->full_name ? 'مسئول: '.$task->assignee->full_name : 'نیازمند تعیین مسئول' }}</span>
                                <span @class(['font-bold text-danger-text' => $isOverdue])>موعد: <x-ui.date :value="$task->due_date" />{{ $task->due_date ? '' : '—' }}</span>
                            </div>
                        </a>
                    @empty
                        <x-ui.empty-state title="تسکی برای نمایش نیست" description="برای شروع، فهرست تسک‌ها را باز کنید و یک کار جدید بسازید.">
                            <a href="{{ route('tasks.index') }}" wire:navigate class="inline-flex min-h-11 items-center rounded-button bg-accent px-4 text-body-sm font-bold text-surface">رفتن به تسک‌ها</a>
                        </x-ui.empty-state>
                    @endforelse
                </div>
            </section>

            <section aria-labelledby="dashboard-projects-heading">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 id="dashboard-projects-heading" class="text-heading-lg font-semibold text-text">پروژه‌های فعال</h2>
                    <a href="{{ route('projects.index') }}" wire:navigate class="inline-flex min-h-11 shrink-0 items-center rounded-control px-2 text-body-sm font-semibold text-text-muted transition hover:bg-surface-muted hover:text-text">مشاهده همه</a>
                </div>
                <div class="divide-y divide-border overflow-hidden rounded-surface border border-border bg-surface">
                    @forelse($recentProjects as $project)
                        <a href="{{ route('projects.show', $project) }}" wire:navigate class="flex min-h-11 flex-col gap-2 p-4 transition hover:bg-surface-muted sm:flex-row sm:items-center sm:justify-between sm:p-5">
                            <div class="min-w-0">
                                <h3 class="truncate font-bold text-text">{{ $project->name }}</h3>
                                @if($project->description)<p class="mt-1 line-clamp-1 text-body-sm text-text-muted">{{ $project->description }}</p>@endif
                            </div>
                            <div class="flex shrink-0 items-center gap-4 text-caption text-text-muted">
                                <x-ui.badge :tone="$project->isActive() ? 'success' : 'neutral'">{{ $project->isActive() ? 'فعال' : 'تکمیل‌شده' }}</x-ui.badge>
                                <span>بروزرسانی <x-ui.date :value="$project->updated_at" datetime /></span>
                            </div>
                        </a>
                    @empty
                        <x-ui.empty-state title="پروژه‌ای برای نمایش نیست" description="پس از ایجاد یا عضویت در یک پروژه، فعالیت‌های آن را اینجا خواهید دید.">
                            <a href="{{ route('projects.index') }}" wire:navigate class="inline-flex min-h-11 items-center rounded-button bg-accent px-4 text-body-sm font-bold text-surface">رفتن به پروژه‌ها</a>
                        </x-ui.empty-state>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="border-t border-border pt-6 xl:border-t-0 xl:border-r xl:pr-8 xl:pt-0" aria-labelledby="dashboard-activity-heading">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="dashboard-activity-heading" class="text-heading-lg font-semibold text-text">فعالیت‌های اخیر</h2>
            </div>
            <div class="space-y-4">
            @forelse($recentActivities as $activity)
                <div class="relative border-r-2 border-info/20 pr-4">
                    <span class="absolute -end-[7px] top-1.5 h-3 w-3 rounded-full border-2 border-page bg-primary" aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <div><span class="font-semibold text-text">{{ $activity->actor?->full_name ?? 'سیستم' }}</span> <span class="text-body-sm text-text-muted">{{ __('tasks::messages.activity_actions.'.$activity->action) }}</span></div>
                        <time class="mt-1 block text-caption text-text-muted"><x-ui.date :value="$activity->created_at" datetime /></time>
                    </div>
                </div>
            @empty
                <p class="text-body-sm text-text-muted">هنوز فعالیتی ثبت نشده است.</p>
            @endforelse
            </div>
        </section>
    </div>
</div>
