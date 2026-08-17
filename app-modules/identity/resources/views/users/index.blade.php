<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('identity::messages.users')" subtitle="مدیریت کاربران مشتریان و وضعیت دسترسی آن‌ها.">
        <x-slot:actions>
            <x-ui.button :href="route('users.create')" icon="fa-user-plus" wire:navigate>{{ __('identity::messages.new_user') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="grid w-full gap-3 md:grid-cols-3">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('identity::messages.search_users')" />
            <x-ui.select name="client" wire:model.live="client">
                <option value="">همه مشتریان</option>
                @foreach($clients as $clientItem)
                    <option value="{{ $clientItem->id }}">{{ $clientItem->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select name="status" wire:model.live="status">
                <option value="">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="inactive">غیرفعال</option>
            </x-ui.select>
        </div>
    </x-ui.filter-bar>

    <div class="divide-y divide-border border-y border-border ui-loading-stable" wire:loading.class="opacity-60" wire:target="q,client,status">
        @forelse($users as $user)
            <a href="{{ route('users.show', $user) }}" wire:navigate wire:key="user-{{ $user->id }}" class="block min-h-11 bg-surface px-1 py-4 transition hover:bg-surface-muted focus-visible:bg-surface-muted sm:px-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="break-words font-semibold text-text">{{ $user->full_name }}</div>
                        <div dir="ltr" class="mt-1 truncate text-right text-body-sm text-text-muted">{{ $user->email }}</div>
                    </div>
                    <x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
                </div>
                <dl class="mt-3 grid gap-2 text-body-sm text-text-muted sm:grid-cols-3">
                    <div><dt class="text-caption">مشتری</dt><dd class="mt-1 truncate font-semibold text-text">{{ $user->client?->name ?? '—' }}</dd></div>
                    <div><dt class="text-caption">موبایل</dt><dd dir="ltr" class="mt-1 truncate text-right font-semibold text-text">{{ $user->mobile ?: '—' }}</dd></div>
                    <div><dt class="text-caption">آخرین ورود</dt><dd class="mt-1 font-semibold text-text"><x-ui.date :value="$user->last_login_at" datetime />{{ $user->last_login_at ? '' : '—' }}</dd></div>
                </dl>
            </a>
        @empty
            <x-ui.empty-state title="کاربری پیدا نشد" description="فیلترها را تغییر دهید یا کاربر جدیدی بسازید." />
        @endforelse
    </div>

    <div class="mt-5">{{ $users->links() }}</div>
</div>
