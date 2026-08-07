@extends('layouts.app')

@section('title', $task ? __('tasks::messages.edit_task') : __('tasks::messages.new_task'))

@section('content')
    @php($pageTitle = $task ? __('tasks::messages.edit_task') : __('tasks::messages.new_task'))
    <x-ui.page-header :title="$pageTitle" />

    <form class="max-w-5xl" method="POST" enctype="multipart/form-data" action="{{ $task ? route('tasks.update', $task['id']) : route('tasks.store') }}">
        @csrf
        @if($task) @method('PUT') @endif

        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" :label="__('app.title')" :value="$task['title'] ?? ''" required />
                    <x-ui.select name="project_id" :label="__('tasks::messages.project')" required>
                        <option value="">—</option>
                        @foreach($options['projects'] as $project)<option value="{{ $project['id'] }}" @selected((string) old('project_id', $task['project_id'] ?? request('project')) === (string) $project['id'])>{{ $project['name'] }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.select name="assigned_to" :label="__('tasks::messages.assignee')">
                        <option value="">{{ __('tasks::messages.unassigned') }}</option>
                        @foreach($options['members'] as $member)<option value="{{ $member['id'] }}" @selected((string) old('assigned_to', $task['assigned_to'] ?? '') === (string) $member['id'])>{{ $member['name'] }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.select name="priority" :label="__('tasks::messages.priority')">
                        @foreach($priorities as $priority)<option value="{{ $priority->value }}" @selected(old('priority', $task['priority'] ?? 'medium') === $priority->value)>{{ __('tasks::messages.priority.'.$priority->value) }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.select name="status" :label="__('app.status')">
                        @foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $task['status'] ?? 'todo') === $status->value)>{{ __('tasks::messages.status.'.$status->value) }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.input name="due_at" :label="__('tasks::messages.due_at')" type="datetime-local" dir="ltr" :value="$task['due_at'] ?? ''" />
                    <x-ui.input name="estimated_minutes" :label="__('tasks::messages.estimated_minutes')" type="number" min="0" :value="$task['estimated_minutes'] ?? ''" />
                    <x-ui.input name="spent_minutes" :label="__('tasks::messages.spent_minutes')" type="number" min="0" :value="$task['spent_minutes'] ?? ''" />
                </div>

                <x-ui.checkbox name="is_customer_visible" :label="__('tasks::messages.customer_visible')" :checked="(bool) old('is_customer_visible', $task['is_customer_visible'] ?? false)" />
                <x-ui.textarea name="description" :label="__('app.description')" :value="$task['description'] ?? ''" />
                <x-ui.input name="attachments[]" :label="__('tasks::messages.add_attachments')" type="file" multiple />

                <x-ui.form-actions>
                    <x-ui.button type="submit">{{ __('app.save') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('tasks.index')">{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
@endsection
