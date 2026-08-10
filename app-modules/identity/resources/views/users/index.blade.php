<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('identity::messages.users')">
        <x-slot:actions>
            <x-ui.button :href="route('users.create')" icon="fa-user-plus" wire:navigate>{{ __('identity::messages.new_user') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('identity::messages.search_users')" />
        </div>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q">
        <thead>
            <tr>
                <th>{{ __('app.name_label') }}</th>
                <th>{{ __('app.email') }}</th>
                <th>{{ __('app.mobile') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr wire:key="user-{{ $user['id'] }}">
                    <td class="font-semibold">{{ $user['full_name'] }}</td>
                    <td dir="ltr" class="text-right">{{ $user['email'] }}</td>
                    <td dir="ltr" class="text-right">{{ $user['mobile'] ?: '—' }}</td>
                    <td><x-ui.badge :tone="$user['is_active'] ? 'success' : 'neutral'">{{ $user['is_active'] ? __('app.active') : __('app.inactive') }}</x-ui.badge></td>
                    <td>
                        <x-ui.button size="sm" variant="secondary" :href="route('users.edit', $user['id'])" icon="fa-pen-to-square" wire:navigate>{{ __('app.edit') }}</x-ui.button>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="5" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
