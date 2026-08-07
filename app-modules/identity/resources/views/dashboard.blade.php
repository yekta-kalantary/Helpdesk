@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-black">{{ __('app.dashboard') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.dashboard_welcome') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card"><div class="text-sm text-slate-500">{{ __('identity::messages.active_customers') }}</div><div class="mt-2 text-3xl font-black">{{ number_format($metrics['customers']) }}</div></div>
        <div class="card"><div class="text-sm text-slate-500">{{ __('identity::messages.active_projects') }}</div><div class="mt-2 text-3xl font-black">{{ number_format($metrics['projects']) }}</div></div>
        <div class="card"><div class="text-sm text-slate-500">{{ __('identity::messages.open_tasks') }}</div><div class="mt-2 text-3xl font-black">{{ number_format($metrics['open_tasks']) }}</div></div>
        <div class="card"><div class="text-sm text-slate-500">{{ __('identity::messages.open_tickets') }}</div><div class="mt-2 text-3xl font-black">{{ number_format($metrics['open_tickets']) }}</div></div>
    </div>
@endsection
