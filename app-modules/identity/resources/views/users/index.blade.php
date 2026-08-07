<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('identity::messages.users')">
        <x-slot:actions>
            @can('users.create')
                <x-ui.button :href="route('users.create')" wire:navigate>{{ __('identity::messages.new_user') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('identity::messages.search_users')" />
        </div>
        <span class="pb-2 text-xs text-slate-500" wire:loading wire:target="q">{{ __('app.loading') }}</span>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q,delete">
        <thead>
            <tr>
                <th>{{ __('app.name_label') }}</th>
                <th>{{ __('app.email') }}</th>
                <th>{{ __('app.mobile') }}</th>
                <th>{{ __('identity::messages.role') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr wire:key="user-{{ $user['id'] }}">
                    <td class="font-semibold">{{ $user['full_name'] }}</td>
                    <td dir="ltr" class="text-right">{{ $user['email'] }}</td>
                    <td dir="ltr" class="text-right">{{ $user['mobile'] }}</td>
                    <td>
                        @if($user['role'])
                            <x-ui.badge>{{ $user['role'] }}</x-ui.badge>
                        @else
                            <span class="text-xs text-slate-500">—</span>
                        @endif
                    </td>
                    <td><x-ui.badge :tone="$user['is_active'] ? 'success' : 'neutral'">{{ $user['is_active'] ? __('app.active') : __('app.inactive') }}</x-ui.badge></td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            @can('users.update')
                                <x-ui.button size="sm" variant="secondary" :href="route('users.edit', $user['id'])" wire:navigate>{{ __('app.edit') }}</x-ui.button>
                            @endcan

                            @can('users.delete')
                                @if(auth()->id() !== $user['id'])
                                    <x-ui.button size="sm" variant="danger" wire:click="delete({{ $user['id'] }})" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="delete({{ $user['id'] }})">{{ __('app.delete') }}</x-ui.button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="6" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
