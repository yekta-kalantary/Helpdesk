@extends('layouts.app')

@section('title', __('settings::messages.smtp'))

@section('content')
    <x-ui.page-header :title="__('settings::messages.smtp')" :subtitle="__('settings::messages.local_fallback')" />

    <form class="max-w-3xl" method="POST" action="{{ route('settings.smtp.update') }}">
        @csrf
        @method('PUT')

        <x-ui.card>
            <div class="space-y-5">
                <x-ui.checkbox name="enabled" :label="__('settings::messages.enabled')" :checked="(bool) old('enabled', $settings['enabled'])" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="host" :label="__('settings::messages.host')" dir="ltr" :value="$settings['host']" />
                    <x-ui.input name="port" :label="__('settings::messages.port')" type="number" min="1" max="65535" dir="ltr" :value="$settings['port']" required />
                    <x-ui.input name="username" :label="__('settings::messages.username')" dir="ltr" :value="$settings['username']" />
                    <x-ui.input name="password" :label="__('settings::messages.password')" type="password" autocomplete="new-password" :hint="$settings['password_configured'] ? __('settings::messages.password_configured') : null" />
                    <x-ui.select name="scheme" :label="__('settings::messages.scheme')">
                        <option value="" @selected(old('scheme', $settings['scheme']) === null || old('scheme', $settings['scheme']) === '')>{{ __('settings::messages.scheme.auto') }}</option>
                        <option value="smtp" @selected(old('scheme', $settings['scheme']) === 'smtp')>{{ __('settings::messages.scheme.smtp') }}</option>
                        <option value="smtps" @selected(old('scheme', $settings['scheme']) === 'smtps')>{{ __('settings::messages.scheme.smtps') }}</option>
                    </x-ui.select>
                    <x-ui.input name="from_address" :label="__('settings::messages.from_address')" type="email" dir="ltr" :value="$settings['from_address']" required />
                    <div class="sm:col-span-2"><x-ui.input name="from_name" :label="__('settings::messages.from_name')" :value="$settings['from_name']" required /></div>
                </div>

                <x-ui.form-actions><x-ui.button type="submit">{{ __('app.save') }}</x-ui.button></x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
@endsection
