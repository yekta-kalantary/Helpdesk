@extends('layouts.app')

@section('title', $customer ? __('customers::messages.edit_customer') : __('customers::messages.new_customer'))

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-black">{{ $customer ? __('customers::messages.edit_customer') : __('customers::messages.new_customer') }}</h1></div>

    <form class="card max-w-4xl space-y-5" method="POST" action="{{ $customer ? route('customers.update', $customer['id']) : route('customers.store') }}">
        @csrf
        @if($customer) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="name">{{ __('app.name_label') }}</label><input id="name" name="name" value="{{ old('name', $customer['name'] ?? '') }}" required></div>
            <div><label for="company">{{ __('customers::messages.company') }}</label><input id="company" name="company" value="{{ old('company', $customer['company'] ?? '') }}"></div>
            <div><label for="email">{{ __('app.email') }}</label><input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $customer['email'] ?? '') }}" required></div>
            <div><label for="phone">{{ __('customers::messages.phone') }}</label><input id="phone" name="phone" dir="ltr" value="{{ old('phone', $customer['phone'] ?? '') }}"></div>
            <div><label for="status">{{ __('app.status') }}</label><select id="status" name="status">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $customer['status'] ?? 'active') === $status->value)>{{ __('customers::messages.status.'.$status->value) }}</option>@endforeach</select></div>
        </div>

        <div><label for="notes">{{ __('customers::messages.notes') }}</label><textarea id="notes" name="notes">{{ old('notes', $customer['notes'] ?? '') }}</textarea></div>

        <div class="rounded-xl border border-slate-200 p-4">
            <label class="flex items-center gap-2"><input class="h-4 w-4" type="checkbox" name="portal_enabled" value="1" @checked(old('portal_enabled', (bool) ($customer['user_id'] ?? false)))><span>{{ __('customers::messages.portal_enabled') }}</span></label>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><label for="portal_password">{{ __('customers::messages.portal_password') }}</label><input id="portal_password" name="portal_password" type="password">@if($customer)<p class="mt-1 text-xs text-slate-500">{{ __('customers::messages.portal_password_hint') }}</p>@endif</div>
                <div><label for="portal_password_confirmation">{{ __('customers::messages.portal_password_confirmation') }}</label><input id="portal_password_confirmation" name="portal_password_confirmation" type="password"></div>
            </div>
        </div>

        <div class="flex gap-2"><button class="btn-primary" type="submit">{{ __('app.save') }}</button><a class="btn-secondary" href="{{ route('customers.index') }}">{{ __('app.cancel') }}</a></div>
    </form>
@endsection
