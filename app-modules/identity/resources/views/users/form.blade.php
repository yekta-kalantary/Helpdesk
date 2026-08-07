@extends('layouts.app')

@section('title', $user ? __('identity::messages.edit_user') : __('identity::messages.new_user'))

@section('content')
    @php($pageTitle = $user ? __('identity::messages.edit_user') : __('identity::messages.new_user'))
    <x-ui.page-header :title="$pageTitle" />

    <form class="max-w-3xl" method="POST" action="{{ $user ? route('users.update', $user['id']) : route('users.store') }}">
        @csrf
        @if($user) @method('PUT') @endif

        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$user['name'] ?? ''" required />
                    <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$user['email'] ?? ''" required />
                    <x-ui.input name="password" :label="__('app.password')" type="password" :required="! $user" :hint="$user ? __('identity::messages.leave_password_blank') : null" />
                    <x-ui.input name="password_confirmation" :label="__('identity::messages.password_confirmation')" type="password" :required="! $user" />
                </div>

                <x-ui.select name="role" :label="__('identity::messages.role')" :hint="__('identity::messages.single_role_hint')" required>
                    <option value="">{{ __('identity::messages.select_role') }}</option>
                    @foreach($roles as $role)<option value="{{ $role['name'] }}" @selected(old('role', $user['role'] ?? '') === $role['name'])>{{ $role['name'] }}</option>@endforeach
                </x-ui.select>

                <x-ui.checkbox name="is_active" :label="__('identity::messages.is_active')" :checked="(bool) old('is_active', $user['is_active'] ?? true)" />

                <x-ui.form-actions>
                    <x-ui.button type="submit">{{ __('app.save') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('users.index')">{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
@endsection
