<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.name')) - {{ __('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="min-h-screen lg:flex">
    <aside class="border-b border-slate-200 bg-white lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-l">
        <div class="border-b border-slate-200 p-5">
            <a href="{{ route('dashboard') }}" class="text-lg font-black text-slate-900">{{ __('app.name') }}</a>
            @auth
                <p class="mt-1 text-xs text-slate-500">{{ auth()->user()->name }}</p>
            @endauth
        </div>
        @auth
            <nav class="space-y-1 p-3 text-sm">
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a>
                @can('customers.view')<a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('customers.index') }}">{{ __('app.customers') }}</a>@endcan
                @can('projects.view')<a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('projects.index') }}">{{ __('app.projects') }}</a>@endcan
                @can('tasks.view')<a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('tasks.index') }}">{{ __('app.tasks') }}</a>@endcan
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('tickets.index') }}">{{ __('app.tickets') }}</a>
                @can('users.view')<a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('users.index') }}">{{ __('app.users') }}</a>@endcan
                @can('roles.view')<a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('roles.index') }}">{{ __('app.roles_permissions') }}</a>@endcan
                @can('settings.manage')<a class="block rounded-lg px-3 py-2 hover:bg-slate-100" href="{{ route('settings.smtp.edit') }}">{{ __('app.settings') }}</a>@endcan
            </nav>
            <div class="p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-secondary w-full" type="submit">{{ __('app.logout') }}</button>
                </form>
            </div>
        @endauth
    </aside>

    <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
        @if (session('success'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
