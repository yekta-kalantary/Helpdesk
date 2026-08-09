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
<body>
    <main class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-xl">
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
