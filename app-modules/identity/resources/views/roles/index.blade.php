@extends('layouts.app')

@section('title', __('app.roles_permissions'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-black">{{ __('app.roles_permissions') }}</h1></div>
        @can('roles.create')<a class="btn-primary" href="{{ route('roles.create') }}">{{ __('identity::messages.new_role') }}</a>@endcan
    </div>

    <div class="space-y-6">
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
                            @else
                                <span class="text-xs text-slate-500">{{ __('identity::messages.system_role_locked') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="mb-4">
                <h2 class="font-bold">{{ __('identity::messages.permission_catalog') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.permission_catalog_hint') }}</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach(collect($permissions)->groupBy('module') as $module => $modulePermissions)
                    <section class="rounded-xl border border-slate-200 p-4">
                        <h3 class="mb-3 font-bold">{{ __('identity::messages.permission_modules.'.$module) }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($modulePermissions as $permission)
                                <span class="badge" dir="ltr">{{ $permission['name'] }}</span>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection
