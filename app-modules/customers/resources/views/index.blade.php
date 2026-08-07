@extends('layouts.app')

@section('title', __('customers::messages.customers'))

@section('content')
    <x-ui.page-header :title="__('customers::messages.customers')">
        @can('customers.create')
            <x-slot:actions><x-ui.button :href="route('customers.create')">{{ __('customers::messages.new_customer') }}</x-ui.button></x-slot:actions>
        @endcan
    </x-ui.page-header>

    <x-ui.filter-bar>
        <div class="min-w-0 flex-1"><x-ui.input name="q" :value="request('q')" :placeholder="__('customers::messages.search_placeholder')" /></div>
        <x-ui.button variant="secondary" type="submit">{{ __('app.search') }}</x-ui.button>
    </x-ui.filter-bar>

    <x-ui.table>
        <thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('customers::messages.company') }}</th><th>{{ __('app.email') }}</th><th>{{ __('customers::messages.phone') }}</th><th>{{ __('app.status') }}</th><th>{{ __('customers::messages.portal') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
        <tbody>
        @forelse($customers as $customer)
            <tr>
                <td class="font-semibold">{{ $customer['name'] }}</td>
                <td>{{ $customer['company'] ?: '—' }}</td>
                <td dir="ltr" class="text-right">{{ $customer['email'] }}</td>
                <td dir="ltr" class="text-right">{{ $customer['phone'] ?: '—' }}</td>
                <td><x-ui.badge>{{ __('customers::messages.status.'.$customer['status']) }}</x-ui.badge></td>
                <td><x-ui.badge :tone="$customer['portal_active'] ? 'success' : 'neutral'">{{ $customer['portal_active'] ? __('app.active') : __('app.inactive') }}</x-ui.badge></td>
                <td>
                    <div class="flex flex-wrap gap-2">
                        @can('customers.update')<x-ui.button size="sm" variant="secondary" :href="route('customers.edit', $customer['id'])">{{ __('app.edit') }}</x-ui.button>@endcan
                        @can('customers.delete')<form method="POST" action="{{ route('customers.destroy', $customer['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<x-ui.button size="sm" variant="danger" type="submit">{{ __('app.delete') }}</x-ui.button></form>@endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-ui.empty-row colspan="7" />
        @endforelse
        </tbody>
    </x-ui.table>
@endsection
