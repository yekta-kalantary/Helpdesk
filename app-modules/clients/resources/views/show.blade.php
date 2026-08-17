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

    <section class="rounded-surface border border-border bg-surface p-4 sm:p-6" aria-labelledby="client-summary-heading">
        <div class="flex flex-wrap items-center gap-2">
            <h2 id="client-summary-heading" class="text-heading-lg font-semibold text-text">نمای کلی مشتری</h2>
            <x-ui.badge :tone="$client->status->value === 'active' ? 'success' : 'neutral'">{{ $client->status->value === 'active' ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
        </div>
        <dl class="mt-4 grid gap-4 sm:grid-cols-3">
            <div><dt class="text-body-sm text-text-muted">کاربران مشتری</dt><dd class="mt-1 text-heading-xl font-semibold text-primary">{{ $client->users_count }}</dd></div>
            <div><dt class="text-body-sm text-text-muted">پروژه‌ها</dt><dd class="mt-1 text-heading-xl font-semibold text-primary">{{ $client->projects_count }}</dd></div>
            <div><dt class="text-body-sm text-text-muted">پروژه فعال</dt><dd class="mt-1 text-heading-xl font-semibold text-primary">{{ $client->active_projects_count }}</dd></div>
        </dl>
    </section>

    <section class="rounded-surface border border-border bg-surface p-4 sm:p-6" aria-labelledby="client-users-heading">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 id="client-users-heading" class="text-heading-lg font-semibold text-text">کاربران</h2>
            <x-ui.button :href="route('users.create', ['client' => $client->id])" variant="secondary" icon="fa-user-plus" wire:navigate>کاربر جدید</x-ui.button>
        </div>
        <div class="divide-y divide-border">
            @forelse($client->users as $user)
                <a class="group flex min-h-11 items-center justify-between gap-3 py-3 transition-colors duration-150 hover:text-primary focus-visible:outline focus-visible:outline-3 focus-visible:outline-focus focus-visible:outline-offset-2" href="{{ route('users.show', $user) }}" wire:navigate>
                    <span class="font-semibold text-text group-hover:text-primary">{{ $user->full_name }}</span>
                    <x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
                </a>
            @empty
                <p class="text-body-sm text-text-muted">کاربری ثبت نشده است.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-surface border border-border bg-surface p-4 sm:p-6" aria-labelledby="client-projects-heading">
        <h2 id="client-projects-heading" class="mb-4 text-heading-lg font-semibold text-text">پروژه‌های اخیر</h2>
        <div class="divide-y divide-border">
            @forelse($projects as $project)
                <a class="group flex min-h-11 items-center justify-between py-3 font-semibold text-text transition-colors duration-150 hover:text-primary focus-visible:outline focus-visible:outline-3 focus-visible:outline-focus focus-visible:outline-offset-2" href="{{ route('projects.show', $project) }}" wire:navigate>{{ $project->name }}<span class="text-body-sm font-normal text-text-muted">مشاهده</span></a>
            @empty
                <p class="text-body-sm text-text-muted">پروژه‌ای ثبت نشده است.</p>
            @endforelse
        </div>
    </section>
</div>
