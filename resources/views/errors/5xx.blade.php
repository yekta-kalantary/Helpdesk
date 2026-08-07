@extends('layouts.guest')

@section('title', __('errors.500.title'))

@section('content')
    <x-ui.card class="text-center">
        <div class="text-5xl font-black text-slate-300">500</div>
        <h1 class="mt-4 text-2xl font-black text-slate-950">{{ __('errors.500.title') }}</h1>
        <p class="mt-3 text-sm leading-7 text-slate-500">{{ __('errors.500.message') }}</p>
        <div class="mt-6"><x-ui.button :href="route('dashboard')">{{ __('errors.back_home') }}</x-ui.button></div>
    </x-ui.card>
@endsection
