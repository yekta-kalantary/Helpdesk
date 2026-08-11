<div class="space-y-6">
    @php($statusLabels = ['todo' => 'برای انجام', 'in_progress' => 'در حال انجام', 'waiting_admin' => 'منتظر ادمین', 'waiting_customer' => 'منتظر مشتری', 'completed' => 'تکمیل‌شده', 'cancelled' => 'لغوشده'])

    <x-ui.page-header :title="__('app.dashboard')">
        <p class="mt-1 text-sm text-slate-500">خلاصه وضعیت پروژه‌ها و تسک‌های قابل دسترسی شما</p>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @if($isAdmin)
            <a href="{{ route('clients.index', ['status' => 'active']) }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                <div class="text-sm font-semibold text-slate-500">مشتریان فعال</div>
                <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($activeClientCount) }}</div>
            </a>
        @endif

        <a href="{{ route('projects.index', ['status' => 'active']) }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="text-sm font-semibold text-slate-500">{{ $isAdmin ? 'پروژه‌های فعال' : 'پروژه‌های فعال من' }}</div>
            <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($activeProjectCount) }}</div>
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="text-sm font-semibold text-slate-500">تسک‌های باز</div>
            <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($openTaskCount) }}</div>
        </a>

        @if($isAdmin)
            <a href="{{ route('tasks.index', ['status' => 'waiting_admin']) }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                <div class="text-sm font-semibold text-slate-500">صف ادمین</div>
                <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($adminQueueCount) }}</div>
            </a>
        @else
            <a href="{{ route('tasks.index', ['assignee' => auth()->id()]) }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                <div class="text-sm font-semibold text-slate-500">واگذار شده به من</div>
                <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($assignedToMeCount) }}</div>
            </a>
        @endif

        <a href="{{ route('tasks.index', ['overdue' => 1]) }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="text-sm font-semibold text-slate-500">عقب‌افتاده</div>
            <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($overdueCount) }}</div>
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('tasks.index', ['status' => 'waiting_admin']) }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-semibold text-slate-500">منتظر ادمین</div>
            <div class="mt-2 text-2xl font-black">{{ number_format($waitingAdminCount) }}</div>
        </a>
        <a href="{{ route('tasks.index', ['status' => 'waiting_customer']) }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-semibold text-slate-500">منتظر مشتری</div>
            <div class="mt-2 text-2xl font-black">{{ number_format($waitingCustomerCount) }}</div>
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
                    <a href="{{ route('projects.show', $project) }}" wire:navigate class="block rounded-xl border border-slate-200 p-3 hover:bg-slate-50">
                        <div class="font-bold">{{ $project->name }}</div>
                        @if($project->description)<p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500">{{ $project->description }}</p>@endif
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
                    <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block rounded-xl border border-slate-200 p-3 hover:bg-slate-50">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-bold">{{ $task->reference }} · {{ $task->title }}</span>
                            <span class="text-xs text-slate-500">{{ $statusLabels[$task->status->value] }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $task->project->name }}</p>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">تسکی وجود ندارد.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.card>
        <h2 class="mb-4 font-black text-slate-950">فعالیت‌های اخیر</h2>
        <div class="space-y-3">
            @forelse($recentActivities as $activity)
                <div class="flex flex-col gap-1 border-b border-slate-100 pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                    <div><span class="font-semibold">{{ $activity->actor?->full_name ?? 'سیستم' }}</span> <span class="text-sm text-slate-600">{{ $activity->action }}</span></div>
                    <time class="text-xs text-slate-500">{{ $activity->created_at?->diffForHumans() }}</time>
                </div>
            @empty
                <p class="text-sm text-slate-500">فعالیتی ثبت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
