<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header title="مشتریان">
        <x-slot:actions>
            <x-ui.button :href="route('clients.create')" icon="fa-building-circle-check" wire:navigate>مشتری جدید</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="فیلترهای سریع">
        <span class="text-caption font-bold text-text-muted">فیلتر سریع</span>
        <button type="button" wire:click="$set('status', 'active')" aria-pressed="{{ $status === 'active' ? 'true' : 'false' }}" @class([
            'ui-filter-chip',
            'border-primary bg-primary-surface text-primary-text' => $status === 'active',
            'bg-surface' => $status !== 'active',
        ])>فعال</button>
        <button type="button" wire:click="$set('status', 'inactive')" aria-pressed="{{ $status === 'inactive' ? 'true' : 'false' }}" @class([
            'ui-filter-chip',
            'border-primary bg-primary-surface text-primary-text' => $status === 'inactive',
            'bg-surface' => $status !== 'inactive',
        ])>غیرفعال</button>
    </div>

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
            <article wire:key="client-mobile-{{ $client->id }}" data-client-id="{{ $client->id }}" data-status="{{ $client->status->value }}" data-count-users="{{ $client->users_count }}" data-count-projects="{{ $client->projects_count }}" class="rounded-surface border border-border bg-surface">
                <a class="ui-list-action ui-list-row group block min-h-11 rounded-surface" href="{{ route('clients.show', $client) }}" wire:navigate>
                    <div class="flex items-start justify-between gap-3">
                        <span class="min-w-0 truncate font-semibold text-text group-hover:text-primary">{{ $client->name }}</span>
                        <x-ui.badge :tone="$client->status->value === 'active' ? 'success' : 'neutral'">{{ $client->status->value === 'active' ? 'فعال' : 'غیرفعال' }}</x-ui.badge>
                    </div>
                    <dl class="ui-list-meta mt-3 flex flex-wrap gap-x-5 gap-y-1">
                        <div><dt class="inline">کاربران: </dt><dd class="inline font-semibold text-text">{{ $client->users_count }}</dd></div>
                        <div><dt class="inline">پروژه‌ها: </dt><dd class="inline font-semibold text-text">{{ $client->projects_count }}</dd></div>
                        <div><dt class="inline">آخرین تغییر: </dt><dd class="inline"><x-ui.date :value="$client->updated_at" /></dd></div>
                    </dl>
                </a>
            </article>
        @empty
            <x-ui.empty-state data-empty-state="clients" title="مشتری‌ای پیدا نشد">
                <a class="inline-flex min-h-11 items-center rounded-control font-semibold text-primary transition-colors duration-150 hover:underline focus-visible:outline focus-visible:outline-3 focus-visible:outline-focus focus-visible:outline-offset-2" href="{{ route('clients.create') }}" wire:navigate>اولین مشتری را بسازید.</a>
            </x-ui.empty-state>
        @endforelse
    </div>

    <div class="hidden sm:block">
        <x-ui.table data-client-table="comparison">
            <thead><tr><th>مشتری</th><th>کاربران</th><th>پروژه‌ها</th><th>{{ __('app.status') }}</th></tr></thead>
            <tbody>
                @forelse($clients as $client)
                    <tr wire:key="client-{{ $client->id }}" class="ui-list-divider">
                        <td><a class="ui-list-action inline-flex min-h-11 items-center rounded-control font-semibold text-text hover:text-primary hover:underline" href="{{ route('clients.show', $client) }}" wire:navigate>{{ $client->name }}</a></td>
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
