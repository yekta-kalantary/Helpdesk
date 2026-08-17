@php
    $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 404;
    $knownStatuses = [403, 404, 419, 422];
    $translationStatus = in_array($status, $knownStatuses, true) ? (string) $status : '4xx';
@endphp

@extends('layouts.guest')

@section('title', __('errors.'.$translationStatus.'.title'))

@section('content')
    <x-ui.card class="text-center">
        <div class="text-display font-semibold text-text-muted" aria-hidden="true">{{ $status }}</div>
        <p class="mt-2 text-label font-semibold text-text-muted">خطای درخواست</p>
        <h1 class="mt-4 text-heading-xl font-semibold text-text">{{ __('errors.'.$translationStatus.'.title') }}</h1>
        <p class="mt-3 text-body-sm leading-7 text-text-muted">{{ __('errors.'.$translationStatus.'.message') }}</p>
        <div class="mt-6"><x-ui.button :href="route('dashboard')">{{ __('errors.back_home') }}</x-ui.button></div>
    </x-ui.card>
@endsection
