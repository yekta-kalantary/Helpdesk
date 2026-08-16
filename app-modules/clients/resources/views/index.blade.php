<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header title="مشتریان">
        <x-slot:actions>
            <x-ui.button :href="route('clients.create')" icon="fa-building-circle-check" wire:navigate>مشتری جدید</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="grid w-full gap-3 sm:grid-cols-2">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" placeholder="جستجو در نام مشتری" />
            <x-ui.select name="status" wire:model.live="status">
                <option value="">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="inactive">غیرفعال</option>
            </x-ui.select>
        </div>
    </x-ui.filter-bar>

    <div class="space-y-3 sm:hidden">
        @forelse($clients as $client)
            <article wire:key="client-mobile-{{ $client->id }}" class="rounded-2xl border border-workspace-border bg-workspace-surface p-4 shadow-[0_8px_24px_rgba(15,92,90,0.06)]">
                <div class="flex items-start justify-between gap-3">
                    <a class="min-w-0 font-bold text-slate-950 hover:text-workspace-teal hover:underline" href="{{ route('clients.show', $client) }}" wire:navigate>{{ $client->name }}</a>
                    <x-ui.badge :tone="$client->status->value === 'active' ? 'success' : 'neutral'">{{ $client->status->value === 'active' ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-500">کاربران</dt><dd class="mt-1 font-bold text-slate-900">{{ $client->users_count }}</dd></div>
                    <div><dt class="text-slate-500">پروژه‌ها</dt><dd class="mt-1 font-bold text-slate-900">{{ $client->projects_count }}</dd></div>
                </dl>
            </article>
        @empty
            <x-ui.card><div class="text-center text-sm text-slate-500">مشتری‌ای پیدا نشد. <a class="font-bold text-workspace-teal hover:underline" href="{{ route('clients.create') }}" wire:navigate>اولین مشتری را بسازید.</a></div></x-ui.card>
        @endforelse
    </div>

    <div class="hidden sm:block">
        <x-ui.table>
            <thead><tr><th>مشتری</th><th>کاربران</th><th>پروژه‌ها</th><th>{{ __('app.status') }}</th></tr></thead>
            <tbody>
                @forelse($clients as $client)
                    <tr wire:key="client-{{ $client->id }}">
                        <td><a class="font-bold text-slate-950 hover:text-workspace-teal hover:underline" href="{{ route('clients.show', $client) }}" wire:navigate>{{ $client->name }}</a></td>
                        <td>{{ $client->users_count }}</td><td>{{ $client->projects_count }}</td>
                        <td><x-ui.badge :tone="$client->status->value === 'active' ? 'success' : 'neutral'">{{ $client->status->value === 'active' ? 'فعال' : 'غیرفعال' }}</x-ui.badge></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="4" />
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-5">{{ $clients->links() }}</div>
</div>
