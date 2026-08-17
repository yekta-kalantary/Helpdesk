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

    <div class="space-y-2 sm:hidden" data-client-list="rows">
        @forelse($clients as $client)
            <article wire:key="client-mobile-{{ $client->id }}" data-client-id="{{ $client->id }}" data-status="{{ $client->status->value }}" data-count-users="{{ $client->users_count }}" data-count-projects="{{ $client->projects_count }}" class="rounded-workspace border border-workspace-border bg-workspace-surface">
                <a class="group block min-h-11 rounded-workspace p-4 transition-colors duration-150 hover:bg-workspace-info-surface focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2" href="{{ route('clients.show', $client) }}" wire:navigate>
                    <div class="flex items-start justify-between gap-3">
                        <span class="min-w-0 truncate font-bold text-workspace-text group-hover:text-workspace-teal">{{ $client->name }}</span>
                        <x-ui.badge :tone="$client->status->value === 'active' ? 'success' : 'neutral'">{{ $client->status->value === 'active' ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
                    </div>
                    <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-workspace-muted">
                        <div><dt class="inline">کاربران: </dt><dd class="inline font-bold text-workspace-text">{{ $client->users_count }}</dd></div>
                        <div><dt class="inline">پروژه‌ها: </dt><dd class="inline font-bold text-workspace-text">{{ $client->projects_count }}</dd></div>
                        <div><dt class="inline">آخرین تغییر: </dt><dd class="inline"><x-ui.date :value="$client->updated_at" /></dd></div>
                    </dl>
                </a>
            </article>
        @empty
            <x-ui.card><div data-empty-state="clients" class="text-center text-sm text-workspace-muted">مشتری‌ای پیدا نشد. <a class="inline-flex min-h-11 items-center rounded-md font-bold text-workspace-teal transition-colors duration-150 hover:underline focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2" href="{{ route('clients.create') }}" wire:navigate>اولین مشتری را بسازید.</a></div></x-ui.card>
        @endforelse
    </div>

    <div class="hidden sm:block">
        <x-ui.table data-client-table="comparison">
            <thead><tr><th>مشتری</th><th>کاربران</th><th>پروژه‌ها</th><th>{{ __('app.status') }}</th></tr></thead>
            <tbody>
                @forelse($clients as $client)
                    <tr wire:key="client-{{ $client->id }}">
                        <td><a class="inline-flex min-h-11 items-center rounded-md font-bold text-workspace-text transition-colors duration-150 hover:text-workspace-teal hover:underline focus-visible:outline focus-visible:outline-3 focus-visible:outline-workspace-focus focus-visible:outline-offset-2" href="{{ route('clients.show', $client) }}" wire:navigate>{{ $client->name }}</a></td>
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
