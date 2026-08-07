@extends('layouts.app')

@section('title', $user ? __('identity::messages.edit_user') : __('identity::messages.new_user'))

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-black">{{ $user ? __('identity::messages.edit_user') : __('identity::messages.new_user') }}</h1></div>

    <form class="card max-w-3xl space-y-5" method="POST" action="{{ $user ? route('users.update', $user['id']) : route('users.store') }}">
        @csrf
        @if($user) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="name">{{ __('app.name_label') }}</label><input id="name" name="name" value="{{ old('name', $user['name'] ?? '') }}" required></div>
            <div><label for="email">{{ __('app.email') }}</label><input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $user['email'] ?? '') }}" required></div>
            <div><label for="password">{{ __('app.password') }}</label><input id="password" name="password" type="password" {{ $user ? '' : 'required' }}>@if($user)<p class="mt-1 text-xs text-slate-500">{{ __('identity::messages.leave_password_blank') }}</p>@endif</div>
            <div><label for="password_confirmation">{{ __('identity::messages.password_confirmation') }}</label><input id="password_confirmation" name="password_confirmation" type="password" {{ $user ? '' : 'required' }}></div>
        </div>

        <div>
            <label for="role">{{ __('identity::messages.role') }}</label>
            <select id="role" name="role" required>
                <option value="">{{ __('identity::messages.select_role') }}</option>
                @foreach($roles as $role)
                    <option value="{{ $role['name'] }}" @selected(old('role', $user['role'] ?? '') === $role['name'])>{{ $role['name'] }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">{{ __('identity::messages.single_role_hint') }}</p>
        </div>

        <label class="flex items-center gap-2"><input class="h-4 w-4" type="checkbox" name="is_active" value="1" @checked(old('is_active', $user['is_active'] ?? true))><span>{{ __('identity::messages.is_active') }}</span></label>

        <div class="flex gap-2"><button class="btn-primary" type="submit">{{ __('app.save') }}</button><a class="btn-secondary" href="{{ route('users.index') }}">{{ __('app.cancel') }}</a></div>
    </form>
@endsection
