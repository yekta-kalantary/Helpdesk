@extends('layouts.app')

@section('title', __('identity::messages.users'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-black">{{ __('identity::messages.users') }}</h1></div>
        @can('users.create')<a class="btn-primary" href="{{ route('users.create') }}">{{ __('identity::messages.new_user') }}</a>@endcan
    </div>

    <form class="mb-4 flex gap-2" method="GET">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('identity::messages.search_users') }}">
        <button class="btn-secondary" type="submit">{{ __('app.search') }}</button>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('app.email') }}</th><th>{{ __('identity::messages.role') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user['name'] }}</td>
                    <td dir="ltr" class="text-right">{{ $user['email'] }}</td>
                    <td>@if($user['role'])<span class="badge">{{ $user['role'] }}</span>@else<span class="text-xs text-slate-500">—</span>@endif</td>
                    <td><span class="badge">{{ $user['is_active'] ? __('app.active') : __('app.inactive') }}</span></td>
                    <td><div class="flex flex-wrap gap-2">
                        @can('users.update')<a class="btn-secondary" href="{{ route('users.edit', $user['id']) }}">{{ __('app.edit') }}</a>@endcan
                        @can('users.delete')
                            @if(auth()->id() !== $user['id'])
                                <form method="POST" action="{{ route('users.destroy', $user['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<button class="btn-danger" type="submit">{{ __('app.delete') }}</button></form>
                            @endif
                        @endcan
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('app.no_records') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
