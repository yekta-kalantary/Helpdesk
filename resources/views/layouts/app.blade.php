<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($resolvedTitle = $title ?? trim($__env->yieldContent('title')) ?: __('app.name'))
    <title>{{ $resolvedTitle }} - {{ __('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
<div class="min-h-screen lg:flex">
    <aside class="border-b border-slate-200 bg-white lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-l">
        <div class="border-b border-slate-200 p-5">
            <a href="{{ route('dashboard') }}" wire:navigate class="text-lg font-black tracking-tight text-slate-950">{{ __('app.name') }}</a>
            @auth
                <p class="mt-1 text-xs font-medium text-slate-500">{{ auth()->user()->name }}</p>
            @endauth
        </div>

        @auth
            <nav class="space-y-1 p-3">
                <x-ui.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('app.dashboard') }}</x-ui.nav-link>

                @can('customers.view')
                    <x-ui.nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">{{ __('app.customers') }}</x-ui.nav-link>
                @endcan

                @can('projects.view')
                    <x-ui.nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">{{ __('app.projects') }}</x-ui.nav-link>
                @endcan

                @can('tasks.view')
                    <x-ui.nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">{{ __('app.tasks') }}</x-ui.nav-link>
                @endcan

                @can('tickets.view')
                    <x-ui.nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">{{ __('app.tickets') }}</x-ui.nav-link>
                @endcan

                @can('reports.view')
                    <x-ui.nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">{{ __('reports::messages.reports') }}</x-ui.nav-link>
                @endcan

                @can('notifications.view')
                    @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
                    <x-ui.nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                        {{ __('identity::notifications.title') }}
                        <x-slot:meta>
                            @if($unreadNotifications > 0)
                                <x-ui.badge :tone="request()->routeIs('notifications.*') ? 'neutral' : 'info'">{{ $unreadNotifications }}</x-ui.badge>
                            @endif
                        </x-slot:meta>
                    </x-ui.nav-link>
                @endcan

                @can('users.view')
                    <x-ui.nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">{{ __('app.users') }}</x-ui.nav-link>
                @endcan

                @can('roles.view')
                    <x-ui.nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">{{ __('app.roles_permissions') }}</x-ui.nav-link>
                @endcan

                @can('settings.manage')
                    <x-ui.nav-link :href="route('settings.smtp.edit')" :active="request()->routeIs('settings.*')">{{ __('app.settings') }}</x-ui.nav-link>
                @endcan
            </nav>

            <div class="border-t border-slate-100 p-3">
                @if(class_exists(\Livewire\Livewire::class))
                    <livewire:identity::logout />
                @else
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button class="w-full" variant="secondary" type="submit">{{ __('app.logout') }}</x-ui.button>
                    </form>
                @endif
            </div>
        @endauth
    </aside>

    <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
        @if(session('success'))
            <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
        @endif

        @if($errors->any())
            <x-ui.alert class="mb-5" tone="danger">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>
</div>
@livewireScripts
</body>
</html>
