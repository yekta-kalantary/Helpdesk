<div>
    <x-ui.page-header :title="__('contacts::messages.contacts')">
        <x-slot:actions>
            @can('contacts.create')
                <x-ui.button :href="route('contacts.create')" icon="fa-plus" wire:navigate>{{ __('contacts::messages.new_contact') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-5">
        <x-ui.input
            name="q"
            :label="__('app.search')"
            :placeholder="__('contacts::messages.search_placeholder')"
            wire:model.live.debounce.350ms="q"
        />
    </x-ui.card>

    <x-ui.table>
        <thead>
            <tr>
                <th>{{ __('app.name_label') }}</th>
                <th>{{ __('app.email') }}</th>
                <th>{{ __('app.mobile') }}</th>
                <th>{{ __('contacts::messages.location') }}</th>
                <th>{{ __('contacts::messages.account') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr wire:key="contact-{{ $contact['id'] }}">
                    <td class="font-semibold">{{ $contact['full_name'] }}</td>
                    <td dir="ltr" class="text-right">{{ $contact['email'] }}</td>
                    <td dir="ltr" class="text-right">{{ $contact['mobile'] }}</td>
                    <td>{{ collect([$contact['province'], $contact['city']])->filter()->implode('، ') ?: '—' }}</td>
                    <td>
                        <x-ui.badge :tone="$contact['account_enabled'] ? 'success' : 'neutral'">
                            {{ $contact['account_enabled'] ? __('app.active') : __('app.inactive') }}
                        </x-ui.badge>
                    </td>
                    <td>
                        @can('contacts.update')
                            <x-ui.button size="sm" variant="secondary" :href="route('contacts.edit', $contact['id'])" icon="fa-pen" wire:navigate>
                                {{ __('app.edit') }}
                            </x-ui.button>
                        @endcan
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="6" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
