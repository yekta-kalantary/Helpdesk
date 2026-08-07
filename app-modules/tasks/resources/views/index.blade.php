@extends('layouts.app')

@section('title', __('tasks::messages.tasks'))

@section('content')
    <x-ui.page-header :title="__('tasks::messages.tasks')">
        @can('tasks.create')<x-slot:actions><x-ui.button :href="route('tasks.create', ['project' => $projectId])">{{ __('tasks::messages.new_task') }}</x-ui.button></x-slot:actions>@endcan
    </x-ui.page-header>

    <x-ui.filter-bar>
        @if($projectId)<input type="hidden" name="project" value="{{ $projectId }}">@endif
        <div class="min-w-0 flex-1"><x-ui.input name="q" :value="request('q')" :placeholder="__('tasks::messages.search_placeholder')" /></div>
        <x-ui.button variant="secondary" type="submit">{{ __('app.search') }}</x-ui.button>
    </x-ui.filter-bar>

    @php($grouped = collect($tasks)->groupBy('status'))
    <div class="grid gap-4 xl:grid-cols-5">
        @foreach($statuses as $status)
            <section class="min-w-0 rounded-2xl border border-slate-200 bg-slate-100/80 p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="font-bold text-slate-900">{{ __('tasks::messages.status.'.$status->value) }}</h2>
                    <x-ui.badge>{{ $grouped->get($status->value, collect())->count() }}</x-ui.badge>
                </div>

                <div class="space-y-3">
                    @forelse($grouped->get($status->value, collect()) as $task)
                        <a href="{{ route('tasks.show', $task['id']) }}" class="group block">
                            <x-ui.card class="transition group-hover:-translate-y-0.5 group-hover:border-slate-300 group-hover:shadow-md">
                                <div class="font-bold text-slate-950">{{ $task['title'] }}</div>
                                <div class="mt-2 text-xs font-medium text-slate-500">{{ $task['project_title'] }}</div>
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    <x-ui.badge>{{ __('tasks::messages.priority.'.$task['priority']) }}</x-ui.badge>
                                    <x-ui.badge>{{ $task['assignee_name'] ?: __('tasks::messages.unassigned') }}</x-ui.badge>
                                </div>
                                @if($task['due_at'])<div class="mt-3 text-xs text-slate-500" dir="ltr">{{ \Illuminate\Support\Carbon::parse($task['due_at'])->format('Y-m-d H:i') }}</div>@endif
                            </x-ui.card>
                        </a>
                    @empty
                        <x-ui.empty-state :description="__('app.no_records')" class="px-3 py-5" />
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection
