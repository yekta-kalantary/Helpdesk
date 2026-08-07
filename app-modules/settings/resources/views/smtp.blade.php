@extends('layouts.app')

@section('title', __('settings::messages.smtp'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-black">{{ __('settings::messages.smtp') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('settings::messages.local_fallback') }}</p>
    </div>

    <form class="card max-w-3xl space-y-5" method="POST" action="{{ route('settings.smtp.update') }}">
        @csrf
        @method('PUT')

        <label class="flex items-center gap-2">
            <input class="h-4 w-4" type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings['enabled']))>
            <span>{{ __('settings::messages.enabled') }}</span>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="host">{{ __('settings::messages.host') }}</label><input id="host" name="host" dir="ltr" value="{{ old('host', $settings['host']) }}"></div>
            <div><label for="port">{{ __('settings::messages.port') }}</label><input id="port" name="port" type="number" min="1" max="65535" dir="ltr" value="{{ old('port', $settings['port']) }}" required></div>
            <div><label for="username">{{ __('settings::messages.username') }}</label><input id="username" name="username" dir="ltr" value="{{ old('username', $settings['username']) }}"></div>
            <div><label for="password">{{ __('settings::messages.password') }}</label><input id="password" name="password" type="password" autocomplete="new-password">@if($settings['password_configured'])<p class="mt-1 text-xs text-slate-500">{{ __('settings::messages.password_configured') }}</p>@endif</div>
            <div><label for="scheme">{{ __('settings::messages.scheme') }}</label><select id="scheme" name="scheme"><option value="" @selected(old('scheme', $settings['scheme']) === null || old('scheme', $settings['scheme']) === '')>{{ __('settings::messages.scheme.auto') }}</option><option value="smtp" @selected(old('scheme', $settings['scheme']) === 'smtp')>{{ __('settings::messages.scheme.smtp') }}</option><option value="smtps" @selected(old('scheme', $settings['scheme']) === 'smtps')>{{ __('settings::messages.scheme.smtps') }}</option></select></div>
            <div><label for="from_address">{{ __('settings::messages.from_address') }}</label><input id="from_address" name="from_address" type="email" dir="ltr" value="{{ old('from_address', $settings['from_address']) }}" required></div>
            <div class="sm:col-span-2"><label for="from_name">{{ __('settings::messages.from_name') }}</label><input id="from_name" name="from_name" value="{{ old('from_name', $settings['from_name']) }}" required></div>
        </div>

        <button class="btn-primary" type="submit">{{ __('app.save') }}</button>
    </form>
@endsection
