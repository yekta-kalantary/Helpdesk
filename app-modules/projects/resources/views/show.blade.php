<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$project->name">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('tasks.index', ['project' => $project->id])" icon="fa-list-check" wire:navigate>تسک‌ها</x-ui.button>
            @if($project->status->value === 'active')
                <x-ui.button :href="route('tasks.create', ['project' => $project->id])" icon="fa-plus" wire:navigate>تسک جدید</x-ui.button>
            @endif
            @if($isAdmin)
                <x-ui.button variant="secondary" :href="route('projects.edit', $project)" icon="fa-pen" wire:navigate>ویرایش</x-ui.button>
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
        <x-ui.card><div class="text-sm text-slate-500">وضعیت</div><div class="mt-2"><x-ui.badge :tone="$project->status->value === 'active' ? 'success' : 'neutral'">{{ $project->status->value === 'active' ? 'فعال' : 'تکمیل‌شده' }}</x-ui.badge></div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">اعضا</div><div class="mt-2 text-2xl font-black">{{ $members->count() }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">تسک باز</div><div class="mt-2 text-2xl font-black">{{ $openTasksCount }}</div></x-ui.card>
    </div>

    @if($project->description)
        <x-ui.card><div class="whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ $project->description }}</div></x-ui.card>
    @endif

    <div class="grid gap-5 xl:grid-cols-2">
        <x-ui.card>
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="font-black">اعضای پروژه</h2>
                @if($isAdmin)<a class="text-sm font-semibold text-slate-600 hover:text-slate-950" href="{{ route('projects.edit', $project) }}" wire:navigate>مدیریت اعضا</a>@endif
            </div>
            <div class="space-y-2">
                @forelse($members as $member)
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="font-bold">{{ $member->full_name }}</div>
                        @if($isAdmin)
                            <div class="mt-1 text-xs text-slate-500"><span dir="ltr">{{ $member->email }}</span>@if($member->mobile) · <span dir="ltr">{{ $member->mobile }}</span>@endif</div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">عضوی ندارد.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-4 font-black">تسک‌های اخیر</h2>
            <div class="space-y-2">
                @forelse($tasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block rounded-xl border border-slate-200 p-3 hover:bg-slate-50">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-bold">{{ $task->reference }} · {{ $task->title }}</span>
                            <span class="text-xs text-slate-500">{{ $task->status->value }}</span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">تسکی ثبت نشده است.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.card>
        <h2 class="mb-4 font-black">فعالیت‌های اخیر</h2>
        <div class="space-y-3">
            @forelse($activities as $activity)
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
