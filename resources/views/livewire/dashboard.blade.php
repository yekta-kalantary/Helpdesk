<div class="space-y-6">
    <x-ui.page-header :title="__('app.dashboard')">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">خلاصه وضعیت پروژه‌ها و تسک‌های قابل دسترسی شما</p>
            <a href="{{ route('notifications.index') }}" wire:navigate class="inline-flex min-h-10 items-center gap-2 self-start rounded-xl border border-workspace-border bg-workspace-surface px-3 text-sm font-semibold text-slate-600 transition hover:border-workspace-teal hover:text-workspace-teal sm:self-auto">
                <i class="fa-light fa-bell" aria-hidden="true"></i>
                اعلان‌ها
            </a>
        </div>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @if($isAdmin)
            <a href="{{ route('clients.index', ['status' => 'active']) }}" wire:navigate class="group">
                <x-ui.stat-card label="مشتریان فعال" :value="number_format($activeClientCount)" hint="مشتریانی که اکنون در جریان کار هستند" icon="fa-users" class="h-full transition group-hover:-translate-y-0.5 group-hover:shadow-md" />
            </a>
        @endif

        <a href="{{ route('projects.index', ['status' => 'active']) }}" wire:navigate class="group">
            <x-ui.stat-card :label="$isAdmin ? 'پروژه‌های فعال' : 'پروژه‌های فعال من'" :value="number_format($activeProjectCount)" hint="پروژه‌هایی که نیاز به پیگیری دارند" icon="fa-folder-open" class="h-full transition group-hover:-translate-y-0.5 group-hover:shadow-md" />
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="group">
            <x-ui.stat-card label="تسک‌های باز" :value="number_format($openTaskCount)" hint="کارهای در حال انجام در پروژه‌ها" icon="fa-list-check" class="h-full transition group-hover:-translate-y-0.5 group-hover:shadow-md" />
        </a>

        @if($isAdmin)
            <a href="{{ route('tasks.index', ['unassigned' => 1]) }}" wire:navigate class="group sm:col-span-2 xl:col-span-1">
                <x-ui.stat-card label="صف بدون مسئول" :value="number_format($unassignedOpenTaskCount)" hint="اولویت بعدی برای واگذاری" icon="fa-inbox" accent="primary" class="h-full transition group-hover:-translate-y-0.5 group-hover:shadow-md" />
            </a>
        @else
            <a href="{{ route('tasks.index', ['assignee' => auth()->id()]) }}" wire:navigate class="group sm:col-span-2 xl:col-span-1">
                <x-ui.stat-card label="واگذار شده به من" :value="number_format($assignedToMeCount)" hint="کارهایی که اکنون با شماست" icon="fa-user-check" accent="primary" class="h-full transition group-hover:-translate-y-0.5 group-hover:shadow-md" />
            </a>
        @endif

        <a href="{{ route('tasks.index', ['overdue' => 1]) }}" wire:navigate class="group">
            <x-ui.stat-card label="عقب‌افتاده" :value="number_format($overdueCount)" hint="کارهایی که موعدشان گذشته است" icon="fa-clock" accent="danger" class="h-full transition group-hover:-translate-y-0.5 group-hover:shadow-md" />
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-ui.card>
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="font-black text-slate-950">پروژه‌های اخیر</h2>
                <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-semibold text-slate-500 hover:text-slate-950">مشاهده همه</a>
            </div>
            <div class="space-y-2">
                @forelse($recentProjects as $project)
                    <a href="{{ route('projects.show', $project) }}" wire:navigate class="flex flex-col gap-3 rounded-xl border border-workspace-border p-3 transition hover:border-workspace-teal hover:bg-teal-50/40 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="truncate font-bold text-slate-900">{{ $project->name }}</div>
                            @if($project->description)<p class="mt-1 line-clamp-1 text-sm leading-6 text-slate-500">{{ $project->description }}</p>@endif
                        </div>
                        <x-ui.badge :tone="$project->isActive() ? 'success' : 'neutral'">{{ $project->isActive() ? 'فعال' : 'تکمیل‌شده' }}</x-ui.badge>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">پروژه‌ای وجود ندارد.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="font-black text-slate-950">تسک‌های اخیر</h2>
                <a href="{{ route('tasks.index') }}" wire:navigate class="text-sm font-semibold text-slate-500 hover:text-slate-950">مشاهده همه</a>
            </div>
            <div class="space-y-2">
                @forelse($recentTasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" wire:navigate class="flex flex-col gap-3 rounded-xl border border-workspace-border p-3 transition hover:border-workspace-teal hover:bg-teal-50/40 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="truncate font-bold text-slate-900">{{ $task->reference }} · {{ $task->title }}</div>
                            <p class="mt-1 text-sm text-slate-500">{{ $task->project->name }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end">
                            <x-ui.badge :tone="$task->isDone() ? 'success' : 'info'">{{ $task->projectStatus->title }}</x-ui.badge>
                            <span @class(['text-xs font-semibold text-red-600' => $task->due_date && $task->due_date->isBefore(today()) && ! $task->isDone(), 'text-slate-500' => ! ($task->due_date && $task->due_date->isBefore(today()) && ! $task->isDone())])>
                                {{ $task->due_date ? 'موعد ' : 'بدون موعد ' }}<x-ui.date :value="$task->due_date" />
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">تسکی وجود ندارد.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.card>
        <h2 class="mb-4 font-black text-slate-950">فعالیت‌های اخیر</h2>
        <div class="space-y-4">
            @forelse($recentActivities as $activity)
                <div class="relative flex gap-3 border-r-2 border-teal-100 pr-4 last:border-r-2">
                    <span class="absolute -right-[7px] top-1.5 h-3 w-3 rounded-full border-2 border-white bg-workspace-teal" aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <div><span class="font-semibold">{{ $activity->actor?->full_name ?? 'سیستم' }}</span> <span class="text-sm text-slate-600">{{ __('tasks::messages.activity_actions.'.$activity->action) }}</span></div>
                        <time class="mt-1 block text-xs text-slate-500"><x-ui.date :value="$activity->created_at" datetime /></time>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">فعالیتی ثبت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
