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
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-workspace-divider bg-workspace-page px-4 lg:hidden">
            <button type="button" data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" aria-label="باز کردن منو" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-workspace-muted transition hover:bg-workspace-neutral-surface hover:text-workspace-text">
                <i class="fa-light fa-bars text-lg" aria-hidden="true"></i>
            </button>

            <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 flex-1 items-center gap-2 truncate text-base font-black tracking-tight text-workspace-text">
                <i class="fa-light fa-gauge-high shrink-0 text-workspace-muted" aria-hidden="true"></i>
                <span class="truncate">{{ $resolvedTitle }}</span>
            </a>

            <a href="{{ route('notifications.index') }}" wire:navigate class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-workspace-muted hover:bg-workspace-neutral-surface hover:text-workspace-text" aria-label="اعلان‌ها">
                <i class="fa-light fa-bell" aria-hidden="true"></i>
                @php($unreadNotificationCount = auth()->user()->unreadNotifications()->count())
                @if($unreadNotificationCount > 0)
                    <span class="absolute -left-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-workspace-accent px-1 text-[10px] font-black leading-5 text-white" aria-label="{{ $unreadNotificationCount }} اعلان خوانده‌نشده">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                @endif
            </a>
        </header>

        <button type="button" data-sidebar-backdrop data-open="false" aria-label="بستن منو" class="pointer-events-none fixed inset-0 z-40 bg-workspace-text/20 opacity-0 transition-opacity duration-200 data-[open=true]:pointer-events-auto data-[open=true]:opacity-100 lg:hidden"></button>
    @endauth

    <aside id="app-sidebar" data-sidebar data-open="false" aria-hidden="true" inert class="sidebar-shell fixed inset-y-0 right-0 z-50 flex w-72 max-w-[86vw] translate-x-full flex-col border-l border-workspace-divider bg-workspace-surface transition-transform duration-200 ease-out data-[open=true]:translate-x-0 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:w-60 lg:max-w-none lg:shrink-0 lg:translate-x-0">
        <div class="flex min-h-16 items-center justify-between gap-3 border-b border-workspace-divider px-4 py-3">
            <div class="min-w-0">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 truncate text-lg font-black tracking-tight text-workspace-text">
                    <i class="fa-light fa-gauge-high shrink-0 text-workspace-muted" aria-hidden="true"></i>
                    <span class="truncate">{{ __('app.name') }}</span>
                </a>
                @auth
                    <a href="{{ route('profile') }}" wire:navigate class="mt-1 block truncate text-xs font-medium text-workspace-muted hover:text-workspace-text">{{ auth()->user()->full_name }}</a>
                @endauth
            </div>

            @auth
                <button type="button" data-sidebar-close aria-label="بستن منو" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-workspace-muted transition hover:bg-workspace-neutral-surface hover:text-workspace-text lg:hidden">
                    <i class="fa-light fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            @endauth
        </div>

        @auth
            <nav aria-label="ناوبری اصلی" class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain p-3">
                <div>
                    <p class="mb-1 px-3 text-[11px] font-black text-workspace-muted">صفحه اصلی</p>
                    <x-ui.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="fa-gauge-high">{{ __('app.dashboard') }}</x-ui.nav-link>
                </div>

                <div>
                    <p class="mb-1 px-3 text-[11px] font-black text-workspace-muted">کارها</p>
                    <div class="space-y-1">
                        <x-ui.nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')" icon="fa-list-check">{{ __('app.tasks') }}</x-ui.nav-link>
                        <x-ui.nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')" icon="fa-bell">اعلان‌ها</x-ui.nav-link>
                    </div>
                </div>

                <div>
                    <p class="mb-1 px-3 text-[11px] font-black text-workspace-muted">فضاها</p>
                    <x-ui.nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" icon="fa-diagram-project">{{ __('app.projects') }}</x-ui.nav-link>
                </div>

                @if(auth()->user()->isAdmin())
                    <div>
                        <p class="mb-1 px-3 text-[11px] font-black text-workspace-muted">مدیریت</p>
                        <div class="space-y-1">
                            <x-ui.nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" icon="fa-building">مشتریان</x-ui.nav-link>
                            <x-ui.nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="fa-users">{{ __('app.users') }}</x-ui.nav-link>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="mt-auto border-t border-workspace-divider p-3">
                <livewire:identity::logout />
            </div>
        @endauth
    </aside>

    <main id="main-content" data-route-focus tabindex="-1" class="min-w-0 flex-1 px-4 py-5 sm:px-6 sm:py-7 lg:px-8 lg:py-8">
        <div class="mx-auto w-full max-w-screen-2xl">
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
        </div>
    </main>
</div>
@livewireScripts
</body>
</html>
