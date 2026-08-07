@extends('layouts.app')

@section('title', __('tickets::messages.new_ticket'))

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-black">{{ __('tickets::messages.new_ticket') }}</h1></div>

    <form class="card max-w-5xl space-y-5" method="POST" enctype="multipart/form-data" action="{{ route('tickets.store') }}">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            @if(! $scope['customer_id'])
                <div><label for="customer_id">{{ __('tickets::messages.customer') }}</label><select id="customer_id" name="customer_id" required><option value="">—</option>@foreach($options['customers'] as $customer)<option value="{{ $customer['id'] }}" @selected((string) old('customer_id') === (string) $customer['id'])>{{ $customer['name'] }}</option>@endforeach</select></div>
            @endif
            <div><label for="project_id">{{ __('tickets::messages.project') }}</label><select id="project_id" name="project_id"><option value="">{{ __('tickets::messages.no_project') }}</option>@foreach($options['projects'] as $project)<option value="{{ $project['id'] }}" @selected((string) old('project_id', request('project')) === (string) $project['id'])>{{ $project['name'] }}</option>@endforeach</select></div>
            <div class="sm:col-span-2"><label for="subject">{{ __('tickets::messages.subject') }}</label><input id="subject" name="subject" value="{{ old('subject') }}" required></div>
            <div><label for="category">{{ __('tickets::messages.category') }}</label><select id="category" name="category">@foreach($categories as $category)<option value="{{ $category->value }}" @selected(old('category', 'general') === $category->value)>{{ __('tickets::messages.category.'.$category->value) }}</option>@endforeach</select></div>
            <div><label for="priority">{{ __('tickets::messages.priority') }}</label><select id="priority" name="priority">@foreach($priorities as $priority)<option value="{{ $priority->value }}" @selected(old('priority', 'medium') === $priority->value)>{{ __('tickets::messages.priority.'.$priority->value) }}</option>@endforeach</select></div>
        </div>
        <div><label for="body">{{ __('tickets::messages.message') }}</label><textarea id="body" name="body" required>{{ old('body') }}</textarea></div>
        <div><label for="attachments">{{ __('tickets::messages.attachments') }}</label><input id="attachments" name="attachments[]" type="file" multiple></div>
        <div class="flex gap-2"><button class="btn-primary" type="submit">{{ __('app.create') }}</button><a class="btn-secondary" href="{{ route('tickets.index') }}">{{ __('app.cancel') }}</a></div>
    </form>
@endsection
