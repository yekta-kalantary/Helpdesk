<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('errors.500.title') }} - {{ __('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center p-4">
    <main class="card w-full max-w-xl text-center">
        <div class="text-5xl font-black text-slate-300">500</div>
        <h1 class="mt-4 text-2xl font-black">{{ __('errors.500.title') }}</h1>
        <p class="mt-3 text-sm leading-7 text-slate-500">{{ __('errors.500.message') }}</p>
        <a class="btn-primary mt-6" href="{{ route('dashboard') }}">{{ __('errors.back_home') }}</a>
    </main>
</body>
</html>
