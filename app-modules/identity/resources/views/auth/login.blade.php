@extends('layouts.guest')

@section('title', __('identity::messages.login_title'))

@section('content')
    <x-ui.card>
        <div class="mb-6">
            <h1 class="text-2xl font-black tracking-tight text-slate-950">{{ __('identity::messages.login_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('app.name') }}</p>
        </div>

        @if($errors->any())
            <x-ui.alert class="mb-4" tone="danger">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf
            <x-ui.input name="email" :label="__('app.email')" type="email" :value="old('email')" required autofocus autocomplete="email" />
            <x-ui.input name="password" :label="__('app.password')" type="password" required autocomplete="current-password" />
            <x-ui.checkbox name="remember" :label="__('app.remember_me')" :checked="(bool) old('remember')" />
            <x-ui.button class="w-full" type="submit">{{ __('app.login') }}</x-ui.button>
        </form>
    </x-ui.card>
@endsection
