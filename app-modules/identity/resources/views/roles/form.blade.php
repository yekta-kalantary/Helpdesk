@extends('layouts.app')

@section('title', $role ? __('identity::messages.edit_role') : __('identity::messages.new_role'))

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-black">{{ $role ? __('identity::messages.edit_role') : __('identity::messages.new_role') }}</h1></div>

    <form class="card max-w-4xl space-y-5" method="POST" action="{{ $role ? route('roles.update', $role['id']) : route('roles.store') }}">
        @csrf
        @if($role) @method('PUT') @endif

        <div class="max-w-md"><label for="name">{{ __('identity::messages.role') }}</label><input id="name" name="name" dir="ltr" value="{{ old('name', $role['name'] ?? '') }}" required></div>

        <div>
            <label>{{ __('identity::messages.permissions') }}</label>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($permissions as $permission)
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3">
                        <input class="h-4 w-4" type="checkbox" name="permissions[]" value="{{ $permission['name'] }}" @checked(in_array($permission['name'], old('permissions', $role['permissions'] ?? []), true))>
                        <span dir="ltr" class="text-xs">{{ $permission['name'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2"><button class="btn-primary" type="submit">{{ __('app.save') }}</button><a class="btn-secondary" href="{{ route('roles.index') }}">{{ __('app.cancel') }}</a></div>
    </form>
@endsection
