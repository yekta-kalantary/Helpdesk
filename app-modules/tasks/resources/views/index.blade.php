@extends('layouts.app')

@section('title', __('tasks::messages.tasks'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black">{{ __('tasks::messages.tasks') }}</h1>
        @can('tasks.create')<a class="btn-primary" href="{{ route('tasks.create', ['project' => $projectId]) }}">{{ __('tasks::messages.new_task') }}</a>@endcan
    </div>

    <form class="mb-5 flex gap-2" method="GET">
        @if($projectId)<input type="hidden" name="project" value="{{ $projectId }}">@endif
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('tasks::messages.search_placeholder') }}">
        <button class="btn-secondary" type="submit">{{ __('app.search') }}</button>
    </form>

    @php($grouped = collect($tasks)->groupBy('status'))
    <div class="grid gap-4 xl:grid-cols-5">
        @foreach($statuses as $status)
            <section class="min-w-0 rounded-xl border border-slate-200 bg-slate-100 p-3">
                <div class="mb-3 flex items-center justify-between"><h2 class="font-bold">{{ __('tasks::messages.status.'.$status->value) }}</h2><span class="badge">{{ $grouped->get($status->value, collect())->count() }}</span></div>
                <div class="space-y-3">
                    @forelse($grouped->get($status->value, collect()) as $task)
                        <a href="{{ route('tasks.show', $task['id']) }}" class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                            <div class="font-bold">{{ $task['title'] }}</div>
                            <div class="mt-2 text-xs text-slate-500">{{ $task['project_title'] }}</div>
                            <div class="mt-3 flex flex-wrap gap-1"><span class="badge">{{ __('tasks::messages.priority.'.$task['priority']) }}</span><span class="badge">{{ $task['assignee_name'] ?: __('tasks::messages.unassigned') }}</span></div>
                            @if($task['due_at'])<div class="mt-3 text-xs text-slate-500" dir="ltr">{{ \Illuminate\Support\Carbon::parse($task['due_at'])->format('Y-m-d H:i') }}</div>@endif
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-500">{{ __('app.no_records') }}</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection
