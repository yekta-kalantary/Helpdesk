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
<body class="min-w-0 overflow-x-hidden">
<div class="min-h-screen lg:flex">
    @auth
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:hidden">
            <button
                type="button"
                data-sidebar-open
                aria-controls="app-sidebar"
                aria-expanded="false"
                aria-label="باز کردن منو"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
            >
                <i class="fa-light fa-bars text-lg" aria-hidden="true"></i>
            </button>

            <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 flex-1 items-center gap-2 truncate text-base font-black tracking-tight text-slate-950">
                <i class="fa-light fa-headset shrink-0 text-slate-500" aria-hidden="true"></i>
                <span class="truncate">{{ __('app.name') }}</span>
            </a>

            <span class="max-w-32 truncate text-xs font-semibold text-slate-500">{{ auth()->user()->full_name }}</span>
        </header>

        <button
            type="button"
            data-sidebar-backdrop
            data-open="false"
            aria-label="بستن منو"
            class="pointer-events-none fixed inset-0 z-40 bg-slate-950/40 opacity-0 transition-opacity duration-200 data-[open=true]:pointer-events-auto data-[open=true]:opacity-100 lg:hidden"
        ></button>
    @endauth

    <aside
        id="app-sidebar"
        data-sidebar
        data-open="false"
        class="fixed inset-y-0 right-0 z-50 flex w-72 max-w-[86vw] translate-x-full flex-col border-l border-slate-200 bg-white shadow-2xl transition-transform duration-200 ease-out data-[open=true]:translate-x-0 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:w-64 lg:max-w-none lg:shrink-0 lg:translate-x-0 lg:shadow-none"
    >
        <div class="flex min-h-16 items-center justify-between gap-3 border-b border-slate-200 p-4 sm:p-5">
            <div class="min-w-0">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 truncate text-lg font-black tracking-tight text-slate-950">
                    <i class="fa-light fa-headset shrink-0 text-slate-500" aria-hidden="true"></i>
                    <span class="truncate">{{ __('app.name') }}</span>
                </a>
                @auth
                    <p class="mt-1 truncate text-xs font-medium text-slate-500">{{ auth()->user()->full_name }}</p>
                @endauth
            </div>

            @auth
                <button
                    type="button"
                    data-sidebar-close
                    aria-label="بستن منو"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-300 lg:hidden"
                >
                    <i class="fa-light fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            @endauth
        </div>

        @auth
            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain p-3">
                <x-ui.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="fa-house">{{ __('app.dashboard') }}</x-ui.nav-link>

                @can('customers.view')
                    <x-ui.nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')" icon="fa-address-book">{{ __('app.customers') }}</x-ui.nav-link>
                @endcan

                @can('projects.view')
                    <x-ui.nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" icon="fa-diagram-project">{{ __('app.projects') }}</x-ui.nav-link>
                @endcan

                @can('tasks.view')
                    <x-ui.nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')" icon="fa-list-check">{{ __('app.tasks') }}</x-ui.nav-link>
                @endcan

                @can('tickets.view')
                    <x-ui.nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')" icon="fa-ticket">{{ __('app.tickets') }}</x-ui.nav-link>
                @endcan

                @can('reports.view')
                    <x-ui.nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="fa-chart-line">{{ __('reports::messages.reports') }}</x-ui.nav-link>
                @endcan

                @can('notifications.view')
                    @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
                    <x-ui.nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')" icon="fa-bell">
                        {{ __('identity::notifications.title') }}
                        <x-slot:meta>
                            @if($unreadNotifications > 0)
                                <x-ui.badge :tone="request()->routeIs('notifications.*') ? 'neutral' : 'info'">{{ $unreadNotifications }}</x-ui.badge>
                            @endif
                        </x-slot:meta>
                    </x-ui.nav-link>
                @endcan

                @can('users.view')
                    <x-ui.nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="fa-users">{{ __('app.users') }}</x-ui.nav-link>
                @endcan

                @can('roles.view')
                    <x-ui.nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" icon="fa-shield-halved">{{ __('app.roles_permissions') }}</x-ui.nav-link>
                @endcan

                @can('settings.manage')
                    <x-ui.nav-link :href="route('settings.smtp.edit')" :active="request()->routeIs('settings.*')" icon="fa-gear">{{ __('app.settings') }}</x-ui.nav-link>
                @endcan
            </nav>

            <div class="mt-auto border-t border-slate-100 p-3">
                <livewire:identity::logout />
            </div>
        @endauth
    </aside>

    <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
        @if($errors->any())
            <x-ui.alert class="mb-5" tone="danger">
                <ul class="list-inside list-disc space-y-1 break-words">
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
