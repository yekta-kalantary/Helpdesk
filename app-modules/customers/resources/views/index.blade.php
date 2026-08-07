<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('customers::messages.customers')">
        <x-slot:actions>
            @can('customers.create')
                <x-ui.button :href="route('customers.create')" wire:navigate>{{ __('customers::messages.new_customer') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('customers::messages.search_placeholder')" />
        </div>
        <span class="pb-2 text-xs text-slate-500" wire:loading wire:target="q">{{ __('app.loading') }}</span>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q,delete">
        <thead>
            <tr>
                <th>{{ __('app.name_label') }}</th>
                <th>{{ __('app.email') }}</th>
                <th>{{ __('app.mobile') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('customers::messages.portal') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr wire:key="customer-{{ $customer['id'] }}">
                    <td class="font-semibold">{{ $customer['full_name'] }}</td>
                    <td dir="ltr" class="text-right">{{ $customer['email'] }}</td>
                    <td dir="ltr" class="text-right">{{ $customer['mobile'] }}</td>
                    <td><x-ui.badge>{{ __('customers::messages.status.'.$customer['status']) }}</x-ui.badge></td>
                    <td><x-ui.badge :tone="$customer['portal_active'] ? 'success' : 'neutral'">{{ $customer['portal_active'] ? __('app.active') : __('app.inactive') }}</x-ui.badge></td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            @can('customers.update')
                                <x-ui.button size="sm" variant="secondary" :href="route('customers.edit', $customer['id'])" wire:navigate>{{ __('app.edit') }}</x-ui.button>
                            @endcan

                            @can('customers.delete')
                                <x-ui.button
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete({{ $customer['id'] }})"
                                    wire:confirm="{{ __('app.confirm_delete') }}"
                                    wire:loading.attr="disabled"
                                    wire:target="delete({{ $customer['id'] }})"
                                >{{ __('app.delete') }}</x-ui.button>
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
