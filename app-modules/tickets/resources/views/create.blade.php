@extends('layouts.app')

@section('title', __('tickets::messages.new_ticket'))

@section('content')
    <x-ui.page-header :title="__('tickets::messages.new_ticket')" />

    <form class="max-w-5xl" method="POST" enctype="multipart/form-data" action="{{ route('tickets.store') }}">
        @csrf
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    @if(! $scope['customer_id'])
                        <x-ui.select name="customer_id" :label="__('tickets::messages.customer')" required>
                            <option value="">—</option>
                            @foreach($options['customers'] as $customer)<option value="{{ $customer['id'] }}" @selected((string) old('customer_id') === (string) $customer['id'])>{{ $customer['name'] }}</option>@endforeach
                        </x-ui.select>
                    @endif

                    <x-ui.select name="project_id" :label="__('tickets::messages.project')">
                        <option value="">{{ __('tickets::messages.no_project') }}</option>
                        @foreach($options['projects'] as $project)<option value="{{ $project['id'] }}" @selected((string) old('project_id', request('project')) === (string) $project['id'])>{{ $project['name'] }}</option>@endforeach
                    </x-ui.select>

                    <div class="sm:col-span-2"><x-ui.input name="subject" :label="__('tickets::messages.subject')" :value="old('subject')" required /></div>

                    <x-ui.select name="category" :label="__('tickets::messages.category')">
                        @foreach($categories as $category)<option value="{{ $category->value }}" @selected(old('category', 'general') === $category->value)>{{ __('tickets::messages.category.'.$category->value) }}</option>@endforeach
                    </x-ui.select>

                    <x-ui.select name="priority" :label="__('tickets::messages.priority')">
                        @foreach($priorities as $priority)<option value="{{ $priority->value }}" @selected(old('priority', 'medium') === $priority->value)>{{ __('tickets::messages.priority.'.$priority->value) }}</option>@endforeach
                    </x-ui.select>
                </div>

                <x-ui.textarea name="body" :label="__('tickets::messages.message')" :value="old('body')" required />
                <x-ui.input name="attachments[]" :label="__('tickets::messages.attachments')" type="file" multiple />

                <x-ui.form-actions>
                    <x-ui.button type="submit">{{ __('app.create') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('tickets.index')">{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
@endsection
