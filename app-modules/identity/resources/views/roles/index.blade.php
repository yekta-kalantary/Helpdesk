<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    @error('role')
        <x-ui.alert class="mb-5" tone="danger">{{ $message }}</x-ui.alert>
    @enderror

    <x-ui.page-header :title="__('app.roles_permissions')">
        <x-slot:actions>
            @can('roles.create')
                <x-ui.button :href="route('roles.create')" wire:navigate>{{ __('identity::messages.new_role') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-6">
        <x-ui.table wire:loading.class="opacity-60" wire:target="delete">
            <thead>
                <tr>
                    <th>{{ __('identity::messages.role') }}</th>
                    <th>{{ __('identity::messages.permissions') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr wire:key="role-{{ $role['id'] }}">
                        <td>
                            <div class="flex flex-wrap items-center gap-2">
                                <span dir="ltr" class="font-semibold">{{ $role['name'] }}</span>
                                <x-ui.badge :tone="$role['system'] ? 'warning' : 'neutral'">{{ $role['system'] ? __('identity::messages.system_role') : __('identity::messages.dynamic_role') }}</x-ui.badge>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($role['permissions'] as $permission)
                                    <x-ui.badge dir="ltr">{{ $permission }}</x-ui.badge>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @if(! $role['system'])
                                <div class="flex flex-wrap gap-2">
                                    @can('roles.update')
                                        <x-ui.button size="sm" variant="secondary" :href="route('roles.edit', $role['id'])" wire:navigate>{{ __('app.edit') }}</x-ui.button>
                                    @endcan
                                    @can('roles.delete')
                                        <x-ui.button size="sm" variant="danger" wire:click="delete({{ $role['id'] }})" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="delete({{ $role['id'] }})">{{ __('app.delete') }}</x-ui.button>
                                    @endcan
                                </div>
                            @else
                                <span class="text-xs text-slate-500">{{ __('identity::messages.system_role_locked') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <x-ui.card :title="__('identity::messages.permission_catalog')" :subtitle="__('identity::messages.permission_catalog_hint')">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach(collect($permissions)->groupBy('module') as $module => $modulePermissions)
                    <section class="rounded-xl border border-slate-200 p-4">
                        <h3 class="mb-3 font-bold text-slate-900">{{ __('identity::messages.permission_modules.'.$module) }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($modulePermissions as $permission)
                                <x-ui.badge dir="ltr">{{ $permission['name'] }}</x-ui.badge>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</div>
