@extends('layouts.app')

@section('title', __('customers::messages.customers'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black">{{ __('customers::messages.customers') }}</h1>
        @can('customers.create')<a class="btn-primary" href="{{ route('customers.create') }}">{{ __('customers::messages.new_customer') }}</a>@endcan
    </div>

    <form class="mb-4 flex gap-2" method="GET">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('customers::messages.search_placeholder') }}">
        <button class="btn-secondary" type="submit">{{ __('app.search') }}</button>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('customers::messages.company') }}</th><th>{{ __('app.email') }}</th><th>{{ __('customers::messages.phone') }}</th><th>{{ __('app.status') }}</th><th>{{ __('customers::messages.portal') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td class="font-semibold">{{ $customer['name'] }}</td>
                    <td>{{ $customer['company'] ?: '—' }}</td>
                    <td dir="ltr" class="text-right">{{ $customer['email'] }}</td>
                    <td dir="ltr" class="text-right">{{ $customer['phone'] ?: '—' }}</td>
                    <td><span class="badge">{{ __('customers::messages.status.'.$customer['status']) }}</span></td>
                    <td><span class="badge">{{ $customer['portal_active'] ? __('app.active') : __('app.inactive') }}</span></td>
                    <td><div class="flex gap-2">
                        @can('customers.update')<a class="btn-secondary" href="{{ route('customers.edit', $customer['id']) }}">{{ __('app.edit') }}</a>@endcan
                        @can('customers.delete')<form method="POST" action="{{ route('customers.destroy', $customer['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<button class="btn-danger" type="submit">{{ __('app.delete') }}</button></form>@endcan
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7">{{ __('app.no_records') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
