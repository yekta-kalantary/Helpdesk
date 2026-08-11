<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$client->name">
        <x-slot:actions>
            <x-ui.button :href="route('clients.edit', $client)" icon="fa-pen" wire:navigate>ویرایش</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.card><div class="text-sm text-slate-500">کاربران مشتری</div><div class="mt-2 text-2xl font-black">{{ $client->users_count }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">پروژه‌ها</div><div class="mt-2 text-2xl font-black">{{ $client->projects_count }}</div></x-ui.card>
        <x-ui.card><div class="text-sm text-slate-500">پروژه فعال</div><div class="mt-2 text-2xl font-black">{{ $client->active_projects_count }}</div></x-ui.card>
    </div>

    <x-ui.card>
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-black">کاربران</h2>
            <x-ui.button :href="route('users.create', ['client' => $client->id])" variant="secondary" icon="fa-user-plus" wire:navigate>کاربر جدید</x-ui.button>
        </div>
        <div class="space-y-2">
            @forelse($client->users as $user)
                <a class="flex items-center justify-between rounded-xl border border-slate-200 p-3 hover:bg-slate-50" href="{{ route('users.show', $user) }}" wire:navigate>
                    <span class="font-bold">{{ $user->full_name }}</span>
                    <x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
                </a>
            @empty
                <p class="text-sm text-slate-500">کاربری ثبت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="mb-4 font-black">پروژه‌های اخیر</h2>
        <div class="space-y-2">
            @forelse($projects as $project)
                <a class="block rounded-xl border border-slate-200 p-3 font-bold hover:bg-slate-50" href="{{ route('projects.show', $project) }}" wire:navigate>{{ $project->name }}</a>
            @empty
                <p class="text-sm text-slate-500">پروژه‌ای ثبت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
