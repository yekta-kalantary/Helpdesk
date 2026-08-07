@extends('layouts.app')

@section('title', $customer ? __('customers::messages.edit_customer') : __('customers::messages.new_customer'))

@section('content')
    @php($pageTitle = $customer ? __('customers::messages.edit_customer') : __('customers::messages.new_customer'))
    <x-ui.page-header :title="$pageTitle" />

    <form class="max-w-4xl" method="POST" action="{{ $customer ? route('customers.update', $customer['id']) : route('customers.store') }}">
        @csrf
        @if($customer) @method('PUT') @endif

        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$customer['name'] ?? ''" required />
                    <x-ui.input name="company" :label="__('customers::messages.company')" :value="$customer['company'] ?? ''" />
                    <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$customer['email'] ?? ''" required />
                    <x-ui.input name="phone" :label="__('customers::messages.phone')" dir="ltr" :value="$customer['phone'] ?? ''" />
                    <x-ui.select name="status" :label="__('app.status')">
                        @foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $customer['status'] ?? 'active') === $status->value)>{{ __('customers::messages.status.'.$status->value) }}</option>@endforeach
                    </x-ui.select>
                </div>

                <x-ui.textarea name="notes" :label="__('customers::messages.notes')" :value="$customer['notes'] ?? ''" />

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <x-ui.checkbox name="portal_enabled" :label="__('customers::messages.portal_enabled')" :checked="(bool) old('portal_enabled', (bool) ($customer['user_id'] ?? false))" />
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="portal_password" :label="__('customers::messages.portal_password')" type="password" :hint="$customer ? __('customers::messages.portal_password_hint') : null" />
                        <x-ui.input name="portal_password_confirmation" :label="__('customers::messages.portal_password_confirmation')" type="password" />
                    </div>
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit">{{ __('app.save') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('customers.index')">{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
@endsection
