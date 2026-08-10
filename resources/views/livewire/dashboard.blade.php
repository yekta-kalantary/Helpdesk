<div class="space-y-6">
    <x-ui.page-header :title="__('app.dashboard')">
        <p class="mt-1 text-sm text-slate-500">{{ __('app.dashboard_summary') }}</p>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @if(auth()->user()->is_admin)
            <a href="{{ route('users.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-slate-500">{{ __('app.users') }}</span>
                    <i class="fa-light fa-users text-lg text-slate-400" aria-hidden="true"></i>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($userCount) }}</div>
            </a>
        @endif

        <a href="{{ route('projects.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-slate-500">{{ auth()->user()->is_admin ? __('app.projects') : __('app.my_projects') }}</span>
                <i class="fa-light fa-diagram-project text-lg text-slate-400" aria-hidden="true"></i>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($projectCount) }}</div>
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-slate-500">{{ __('app.tasks') }}</span>
                <i class="fa-light fa-list-check text-lg text-slate-400" aria-hidden="true"></i>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($taskCount) }}</div>
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-slate-500">{{ __('app.open_tasks') }}</span>
                <i class="fa-light fa-circle-dot text-lg text-slate-400" aria-hidden="true"></i>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($openTaskCount) }}</div>
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-slate-500">{{ __('app.completed_tasks') }}</span>
                <i class="fa-light fa-circle-check text-lg text-slate-400" aria-hidden="true"></i>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-950">{{ number_format($completedTaskCount) }}</div>
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-ui.card>
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="text-base font-black text-slate-950">{{ __('app.recent_projects') }}</h2>
                <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-semibold text-slate-500 transition hover:text-slate-950">{{ __('app.view_all') }}</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentProjects as $project)
                    <div class="py-4 first:pt-0 last:pb-0">
                        <div class="font-bold text-slate-900">{{ $project->title }}</div>
                        @if($project->description)
                            <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500">{{ $project->description }}</p>
                        @endif
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">{{ __('app.no_recent_projects') }}</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="text-base font-black text-slate-950">{{ __('app.recent_tasks') }}</h2>
                <a href="{{ route('tasks.index') }}" wire:navigate class="text-sm font-semibold text-slate-500 transition hover:text-slate-950">{{ __('app.view_all') }}</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentTasks as $task)
                    <div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <div class="truncate font-bold text-slate-900">{{ $task->title }}</div>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $task->project->title }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $task->is_done ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $task->is_done ? __('app.completed') : __('app.open') }}
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">{{ __('app.no_recent_tasks') }}</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>
