@extends('layouts.app')

@section('title', __('identity::messages.users'))

@section('content')
    <x-ui.page-header :title="__('identity::messages.users')">
        @can('users.create')<x-slot:actions><x-ui.button :href="route('users.create')">{{ __('identity::messages.new_user') }}</x-ui.button></x-slot:actions>@endcan
    </x-ui.page-header>

    <x-ui.filter-bar>
        <div class="min-w-0 flex-1"><x-ui.input name="q" :value="request('q')" :placeholder="__('identity::messages.search_users')" /></div>
        <x-ui.button variant="secondary" type="submit">{{ __('app.search') }}</x-ui.button>
    </x-ui.filter-bar>

    <x-ui.table>
        <thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('app.email') }}</th><th>{{ __('identity::messages.role') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td class="font-semibold">{{ $user['name'] }}</td>
                <td dir="ltr" class="text-right">{{ $user['email'] }}</td>
                <td>@if($user['role'])<x-ui.badge>{{ $user['role'] }}</x-ui.badge>@else<span class="text-xs text-slate-500">—</span>@endif</td>
                <td><x-ui.badge :tone="$user['is_active'] ? 'success' : 'neutral'">{{ $user['is_active'] ? __('app.active') : __('app.inactive') }}</x-ui.badge></td>
                <td>
                    <div class="flex flex-wrap gap-2">
                        @can('users.update')<x-ui.button size="sm" variant="secondary" :href="route('users.edit', $user['id'])">{{ __('app.edit') }}</x-ui.button>@endcan
                        @can('users.delete')
                            @if(auth()->id() !== $user['id'])
                                <form method="POST" action="{{ route('users.destroy', $user['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<x-ui.button size="sm" variant="danger" type="submit">{{ __('app.delete') }}</x-ui.button></form>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
        </tbody>
    </x-ui.table>
@endsection
