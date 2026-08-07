@php
    $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 404;
    $knownStatuses = [403, 404, 419, 422];
    $translationStatus = in_array($status, $knownStatuses, true) ? (string) $status : '4xx';
@endphp

@extends('layouts.guest')

@section('title', __('errors.'.$translationStatus.'.title'))

@section('content')
    <x-ui.card class="text-center">
        <div class="text-5xl font-black text-slate-300">{{ $status }}</div>
        <h1 class="mt-4 text-2xl font-black text-slate-950">{{ __('errors.'.$translationStatus.'.title') }}</h1>
        <p class="mt-3 text-sm leading-7 text-slate-500">{{ __('errors.'.$translationStatus.'.message') }}</p>
        <div class="mt-6"><x-ui.button :href="route('dashboard')">{{ __('errors.back_home') }}</x-ui.button></div>
    </x-ui.card>
@endsection
