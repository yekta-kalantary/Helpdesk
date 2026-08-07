<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('identity::messages.login_title') }} - {{ __('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center p-4">
    <div class="card w-full max-w-md">
        <h1 class="mb-1 text-2xl font-black">{{ __('identity::messages.login_title') }}</h1>
        <p class="mb-6 text-sm text-slate-500">{{ __('app.name') }}</p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email">{{ __('app.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div>
                <label for="password">{{ __('app.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <label class="flex items-center gap-2">
                <input class="h-4 w-4" name="remember" type="checkbox" value="1">
                <span>{{ __('app.remember_me') }}</span>
            </label>
            <button class="btn-primary w-full" type="submit">{{ __('app.login') }}</button>
        </form>
    </div>
</body>
</html>
