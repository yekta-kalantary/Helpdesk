<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' | '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-950 antialiased">
<div class="min-h-screen lg:flex">
    <div class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur lg:hidden">
        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('dashboard') }}" class="font-black tracking-tight" wire:navigate>{{ config('app.name') }}</a>
            @auth
                <livewire:identity::logout />
            @endauth
        </div>
        @auth
            <nav class="flex gap-2 overflow-x-auto px-4 pb-3 text-sm">
                @can('projects.view')<x-ui.nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">{{ __('app.projects') }}</x-ui.nav-link>@endcan
                @can('tasks.view')<x-ui.nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">{{ __('app.tasks') }}</x-ui.nav-link>@endcan
                @can('tickets.view')<x-ui.nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">{{ __('app.tickets') }}</x-ui.nav-link>@endcan
                @can('customers.view')<x-ui.nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">{{ __('app.customers') }}</x-ui.nav-link>@endcan
                @can('reports.view')<x-ui.nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">{{ __('app.reports') }}</x-ui.nav-link>@endcan
            </nav>
        @endauth
    </div>

    <aside class="hidden w-64 shrink-0 border-l border-slate-200 bg-white lg:flex lg:min-h-screen lg:flex-col">
        <a href="{{ route('dashboard') }}" class="border-b border-slate-100 px-5 py-5 text-lg font-black tracking-tight" wire:navigate>{{ config('app.name') }}</a>

        @auth
            <nav class="flex-1 space-y-1 p-3">
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
                    <x-ui.nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">{{ __('app.reports') }}</x-ui.nav-link>
                @endcan

                @can('notifications.view')
                    <x-ui.nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">{{ __('app.notifications') }}</x-ui.nav-link>
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
                <livewire:identity::logout />
            </div>
        @endauth
    </aside>

    <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
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
</body>
</html>
