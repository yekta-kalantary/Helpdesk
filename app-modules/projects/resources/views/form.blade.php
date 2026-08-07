@extends('layouts.app')

@section('title', $project ? __('projects::messages.edit_project') : __('projects::messages.new_project'))

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-black">{{ $project ? __('projects::messages.edit_project') : __('projects::messages.new_project') }}</h1></div>

    <form class="card max-w-5xl space-y-5" method="POST" action="{{ $project ? route('projects.update', $project['id']) : route('projects.store') }}">
        @csrf
        @if($project) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="title">{{ __('app.title') }}</label><input id="title" name="title" value="{{ old('title', $project['title'] ?? '') }}" required></div>
            <div><label for="customer_id">{{ __('projects::messages.customer') }}</label><select id="customer_id" name="customer_id" required><option value="">—</option>@foreach($options['customers'] as $customer)<option value="{{ $customer['id'] }}" @selected((string) old('customer_id', $project['customer_id'] ?? '') === (string) $customer['id'])>{{ $customer['name'] }}</option>@endforeach</select></div>
            <div><label for="type">{{ __('projects::messages.type') }}</label><select id="type" name="type" required>@foreach($types as $type)<option value="{{ $type->value }}" @selected(old('type', $project['type'] ?? '') === $type->value)>{{ __('projects::messages.type.'.$type->value) }}</option>@endforeach</select></div>
            <div><label for="status">{{ __('app.status') }}</label><select id="status" name="status" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $project['status'] ?? 'planning') === $status->value)>{{ __('projects::messages.status.'.$status->value) }}</option>@endforeach</select></div>
            <div><label for="starts_at">{{ __('projects::messages.starts_at') }}</label><input id="starts_at" name="starts_at" type="date" dir="ltr" value="{{ old('starts_at', $project['starts_at'] ?? '') }}"></div>
            <div><label for="ends_at">{{ __('projects::messages.ends_at') }}</label><input id="ends_at" name="ends_at" type="date" dir="ltr" value="{{ old('ends_at', $project['ends_at'] ?? '') }}"></div>
        </div>

        <div><label for="description">{{ __('app.description') }}</label><textarea id="description" name="description">{{ old('description', $project['description'] ?? '') }}</textarea></div>

        <div>
            <label>{{ __('projects::messages.members') }}</label>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($options['members'] as $member)
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3"><input class="h-4 w-4" type="checkbox" name="member_ids[]" value="{{ $member['id'] }}" @checked(in_array($member['id'], array_map('intval', old('member_ids', $project['member_ids'] ?? [])), true))><span>{{ $member['name'] }}</span></label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2"><button class="btn-primary" type="submit">{{ __('app.save') }}</button><a class="btn-secondary" href="{{ route('projects.index') }}">{{ __('app.cancel') }}</a></div>
    </form>
@endsection
