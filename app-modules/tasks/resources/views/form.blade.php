@extends('layouts.app')

@section('title', $task ? __('tasks::messages.edit_task') : __('tasks::messages.new_task'))

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-black">{{ $task ? __('tasks::messages.edit_task') : __('tasks::messages.new_task') }}</h1></div>

    <form class="card max-w-5xl space-y-5" method="POST" enctype="multipart/form-data" action="{{ $task ? route('tasks.update', $task['id']) : route('tasks.store') }}">
        @csrf
        @if($task) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="title">{{ __('app.title') }}</label><input id="title" name="title" value="{{ old('title', $task['title'] ?? '') }}" required></div>
            <div><label for="project_id">{{ __('tasks::messages.project') }}</label><select id="project_id" name="project_id" required><option value="">—</option>@foreach($options['projects'] as $project)<option value="{{ $project['id'] }}" @selected((string) old('project_id', $task['project_id'] ?? request('project')) === (string) $project['id'])>{{ $project['name'] }}</option>@endforeach</select></div>
            <div><label for="assigned_to">{{ __('tasks::messages.assignee') }}</label><select id="assigned_to" name="assigned_to"><option value="">{{ __('tasks::messages.unassigned') }}</option>@foreach($options['members'] as $member)<option value="{{ $member['id'] }}" @selected((string) old('assigned_to', $task['assigned_to'] ?? '') === (string) $member['id'])>{{ $member['name'] }}</option>@endforeach</select></div>
            <div><label for="priority">{{ __('tasks::messages.priority') }}</label><select id="priority" name="priority">@foreach($priorities as $priority)<option value="{{ $priority->value }}" @selected(old('priority', $task['priority'] ?? 'medium') === $priority->value)>{{ __('tasks::messages.priority.'.$priority->value) }}</option>@endforeach</select></div>
            <div><label for="status">{{ __('app.status') }}</label><select id="status" name="status">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $task['status'] ?? 'todo') === $status->value)>{{ __('tasks::messages.status.'.$status->value) }}</option>@endforeach</select></div>
            <div><label for="due_at">{{ __('tasks::messages.due_at') }}</label><input id="due_at" name="due_at" type="datetime-local" dir="ltr" value="{{ old('due_at', $task['due_at'] ?? '') }}"></div>
            <div><label for="estimated_minutes">{{ __('tasks::messages.estimated_minutes') }}</label><input id="estimated_minutes" name="estimated_minutes" type="number" min="0" value="{{ old('estimated_minutes', $task['estimated_minutes'] ?? '') }}"></div>
            <div><label for="spent_minutes">{{ __('tasks::messages.spent_minutes') }}</label><input id="spent_minutes" name="spent_minutes" type="number" min="0" value="{{ old('spent_minutes', $task['spent_minutes'] ?? '') }}"></div>
        </div>

        <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3">
            <input class="h-4 w-4" type="checkbox" name="is_customer_visible" value="1" @checked(old('is_customer_visible', $task['is_customer_visible'] ?? false))>
            <span>{{ __('tasks::messages.customer_visible') }}</span>
        </label>

        <div><label for="description">{{ __('app.description') }}</label><textarea id="description" name="description">{{ old('description', $task['description'] ?? '') }}</textarea></div>
        <div><label for="attachments">{{ __('tasks::messages.add_attachments') }}</label><input id="attachments" name="attachments[]" type="file" multiple></div>

        <div class="flex gap-2"><button class="btn-primary" type="submit">{{ __('app.save') }}</button><a class="btn-secondary" href="{{ route('tasks.index') }}">{{ __('app.cancel') }}</a></div>
    </form>
@endsection
