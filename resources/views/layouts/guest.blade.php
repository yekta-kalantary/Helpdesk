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
    <main class="flex min-h-screen items-center justify-center bg-workspace-page px-4 py-8 sm:px-6">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 text-lg font-black tracking-tight text-slate-950">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-workspace-teal text-white"><i class="fa-light fa-gauge-high" aria-hidden="true"></i></span>
                    {{ __('app.name') }}
                </a>
                <p class="mt-2 text-sm text-slate-500">فضای کاری آرام برای مدیریت پروژه‌ها</p>
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
