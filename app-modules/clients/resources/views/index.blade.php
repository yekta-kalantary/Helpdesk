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

    <div class="overflow-x-auto">
        <x-ui.table>
            <thead>
                <tr>
                    <th>مشتری</th>
                    <th>کاربران</th>
                    <th>پروژه‌ها</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr wire:key="client-{{ $client->id }}">
                        <td><a class="font-bold text-slate-950 hover:underline" href="{{ route('clients.show', $client) }}" wire:navigate>{{ $client->name }}</a></td>
                        <td>{{ $client->users_count }}</td>
                        <td>{{ $client->projects_count }}</td>
                        <td>
                            <x-ui.badge :tone="$client->status->value === 'active' ? 'success' : 'neutral'">
                                {{ $client->status->value === 'active' ? 'فعال' : 'غیرفعال' }}
                            </x-ui.badge>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="4" />
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-5">{{ $clients->links() }}</div>
</div>
