@extends('layouts.app')

@section('title', __('projects::messages.projects'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black">{{ __('projects::messages.projects') }}</h1>
        @can('projects.create')<a class="btn-primary" href="{{ route('projects.create') }}">{{ __('projects::messages.new_project') }}</a>@endcan
    </div>

    <form class="mb-4 flex gap-2" method="GET">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('projects::messages.search_placeholder') }}">
        <button class="btn-secondary" type="submit">{{ __('app.search') }}</button>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>{{ __('app.title') }}</th><th>{{ __('projects::messages.customer') }}</th><th>{{ __('projects::messages.type') }}</th><th>{{ __('app.status') }}</th><th>{{ __('projects::messages.progress') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td class="font-semibold">{{ $project['title'] }}</td>
                    <td>{{ $project['customer_name'] }}</td>
                    <td><span class="badge">{{ __('projects::messages.type.'.$project['type']) }}</span></td>
                    <td><span class="badge">{{ __('projects::messages.status.'.$project['status']) }}</span></td>
                    <td><div class="w-32"><div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full bg-slate-900" style="width: {{ $project['progress'] }}%"></div></div><div class="mt-1 text-xs text-slate-500">{{ $project['progress'] }}%</div></div></td>
                    <td><div class="flex flex-wrap gap-2">
                        @can('tasks.view')<a class="btn-secondary" href="{{ route('tasks.index', ['project' => $project['id']]) }}">{{ __('app.tasks') }}</a>@endcan
                        <a class="btn-secondary" href="{{ route('tickets.index', ['project' => $project['id']]) }}">{{ __('app.tickets') }}</a>
                        @can('projects.update')<a class="btn-secondary" href="{{ route('projects.edit', $project['id']) }}">{{ __('app.edit') }}</a>@endcan
                        @can('projects.delete')<form method="POST" action="{{ route('projects.destroy', $project['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<button class="btn-danger" type="submit">{{ __('app.delete') }}</button></form>@endcan
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('app.no_records') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
