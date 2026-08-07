@extends('layouts.app')

@section('title', __('app.roles_permissions'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-black">{{ __('app.roles_permissions') }}</h1></div>
        @can('roles.create')<a class="btn-primary" href="{{ route('roles.create') }}">{{ __('identity::messages.new_role') }}</a>@endcan
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>{{ __('identity::messages.role') }}</th><th>{{ __('identity::messages.permissions') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td><div class="flex items-center gap-2"><span dir="ltr" class="font-semibold">{{ $role['name'] }}</span><span class="badge">{{ $role['system'] ? __('identity::messages.system_role') : __('identity::messages.dynamic_role') }}</span></div></td>
                            <td><div class="flex flex-wrap gap-1">@foreach($role['permissions'] as $permission)<span class="badge" dir="ltr">{{ $permission }}</span>@endforeach</div></td>
                            <td>
                                @if(! $role['system'])
                                    <div class="flex gap-2">
                                        @can('roles.update')<a class="btn-secondary" href="{{ route('roles.edit', $role['id']) }}">{{ __('app.edit') }}</a>@endcan
                                        @can('roles.delete')<form method="POST" action="{{ route('roles.destroy', $role['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<button class="btn-danger" type="submit">{{ __('app.delete') }}</button></form>@endcan
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            @can('roles.create')
                <form class="card space-y-3" method="POST" action="{{ route('permissions.store') }}">
                    @csrf
                    <h2 class="font-bold">{{ __('identity::messages.new_permission') }}</h2>
                    <div><label for="permission_name">{{ __('identity::messages.permission') }}</label><input id="permission_name" name="name" dir="ltr" placeholder="{{ __('identity::messages.permission_name_hint') }}" required></div>
                    <button class="btn-primary" type="submit">{{ __('app.create') }}</button>
                </form>
            @endcan

            <div class="card">
                <h2 class="mb-3 font-bold">{{ __('identity::messages.permissions') }}</h2>
                <div class="space-y-2">
                    @foreach($permissions as $permission)
                        <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 p-2">
                            <span dir="ltr" class="text-xs">{{ $permission['name'] }}</span>
                            @can('roles.delete')<form method="POST" action="{{ route('permissions.destroy', $permission['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<button class="text-xs text-red-600" type="submit">{{ __('app.delete') }}</button></form>@endcan
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
