<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($resolvedTitle = $title ?? trim($__env->yieldContent('title')) ?: __('app.name'))
    <title>{{ $resolvedTitle }} - {{ __('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('fontawesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome/css/light.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome/css/brands.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-w-0 overflow-x-hidden">
    <main class="guest-shell flex min-h-screen items-center justify-center bg-page px-4 py-10 sm:px-6">
        <div class="w-full max-w-md min-w-0">
            <div class="guest-brand mb-8 text-center">
                <a href="{{ route('login') }}" wire:navigate class="inline-flex min-h-11 items-center gap-2 text-lg font-black tracking-tight text-text">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-control bg-primary text-surface"><i class="fa-light fa-gauge-high" aria-hidden="true"></i></span>
                    {{ __('app.name') }}
                </a>
                <p class="mt-2 text-body-sm text-text-muted">فضای کاری آرام برای مدیریت پروژه‌ها</p>
            </div>
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </div>
    </main>
    @livewireScripts
</body>
</html>
