@extends('layouts.guest')

@section('title', __('errors.500.title'))

@section('content')
    <x-ui.card class="text-center">
        <div class="text-display font-semibold text-text-muted" aria-hidden="true">500</div>
        <p class="mt-2 text-label font-semibold text-text-muted">خطای سرویس</p>
        <h1 class="mt-4 text-heading-xl font-semibold text-text">{{ __('errors.500.title') }}</h1>
        <p class="mt-3 text-body-sm leading-7 text-text-muted">{{ __('errors.500.message') }}</p>
        <div class="mt-6"><x-ui.button :href="route('dashboard')">{{ __('errors.back_home') }}</x-ui.button></div>
    </x-ui.card>
@endsection
