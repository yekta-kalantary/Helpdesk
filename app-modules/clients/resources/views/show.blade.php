<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    @php($breadcrumbs = [
        ['label' => 'مشتریان', 'href' => route('clients.index')],
        ['label' => $client->name, 'href' => null],
    ])

    <x-ui.page-header :title="$client->name" subtitle="نمای کلی مشتری، کاربران و پروژه‌های مرتبط." :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button :href="route('clients.edit', $client)" icon="fa-pen" wire:navigate>ویرایش</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="rounded-workspace border border-workspace-border bg-workspace-surface p-4 sm:p-6" aria-labelledby="client-summary-heading">
        <div class="flex flex-wrap items-center gap-2">
            <h2 id="client-summary-heading" class="font-bold text-workspace-text">نمای کلی مشتری</h2>
            <x-ui.badge :tone="$client->status->value === 'active' ? 'success' : 'neutral'">{{ $client->status->value === 'active' ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
        </div>
        <dl class="mt-4 grid gap-4 sm:grid-cols-3">
            <div><dt class="text-sm text-workspace-muted">کاربران مشتری</dt><dd class="mt-1 text-2xl font-black text-workspace-teal">{{ $client->users_count }}</dd></div>
            <div><dt class="text-sm text-workspace-muted">پروژه‌ها</dt><dd class="mt-1 text-2xl font-black text-workspace-teal">{{ $client->projects_count }}</dd></div>
            <div><dt class="text-sm text-workspace-muted">پروژه فعال</dt><dd class="mt-1 text-2xl font-black text-workspace-teal">{{ $client->active_projects_count }}</dd></div>
        </dl>
    </section>

    <section class="rounded-workspace border border-workspace-border bg-workspace-surface p-4 sm:p-6" aria-labelledby="client-users-heading">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 id="client-users-heading" class="font-bold text-workspace-text">کاربران</h2>
            <x-ui.button :href="route('users.create', ['client' => $client->id])" variant="secondary" icon="fa-user-plus" wire:navigate>کاربر جدید</x-ui.button>
        </div>
        <div class="divide-y divide-workspace-divider">
            @forelse($client->users as $user)
                <a class="group flex min-h-11 items-center justify-between gap-3 py-3 transition-colors duration-150 hover:text-workspace-teal focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2" href="{{ route('users.show', $user) }}" wire:navigate>
                    <span class="font-bold text-workspace-text group-hover:text-workspace-teal">{{ $user->full_name }}</span>
                    <x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
                </a>
            @empty
                <p class="text-sm text-workspace-muted">کاربری ثبت نشده است.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-workspace border border-workspace-border bg-workspace-surface p-4 sm:p-6" aria-labelledby="client-projects-heading">
        <h2 id="client-projects-heading" class="mb-4 font-bold text-workspace-text">پروژه‌های اخیر</h2>
        <div class="divide-y divide-workspace-divider">
            @forelse($projects as $project)
                <a class="group flex min-h-11 items-center justify-between py-3 font-bold text-workspace-text transition-colors duration-150 hover:text-workspace-teal focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2" href="{{ route('projects.show', $project) }}" wire:navigate>{{ $project->name }}<span class="text-sm font-normal text-workspace-muted">مشاهده</span></a>
            @empty
                <p class="text-sm text-workspace-muted">پروژه‌ای ثبت نشده است.</p>
            @endforelse
        </div>
    </section>
</div>
