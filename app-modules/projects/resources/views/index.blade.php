@extends('layouts.app')

@section('title', __('projects::messages.projects'))

@section('content')
    <x-ui.page-header :title="__('projects::messages.projects')">
        @can('projects.create')<x-slot:actions><x-ui.button :href="route('projects.create')">{{ __('projects::messages.new_project') }}</x-ui.button></x-slot:actions>@endcan
    </x-ui.page-header>

    <x-ui.filter-bar>
        <div class="min-w-0 flex-1"><x-ui.input name="q" :value="request('q')" :placeholder="__('projects::messages.search_placeholder')" /></div>
        <x-ui.button variant="secondary" type="submit">{{ __('app.search') }}</x-ui.button>
    </x-ui.filter-bar>

    <x-ui.table>
        <thead><tr><th>{{ __('app.title') }}</th><th>{{ __('projects::messages.customer') }}</th><th>{{ __('projects::messages.type') }}</th><th>{{ __('app.status') }}</th><th>{{ __('projects::messages.progress') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
        <tbody>
        @forelse($projects as $project)
            <tr>
                <td class="font-semibold">{{ $project['title'] }}</td>
                <td>{{ $project['customer_name'] }}</td>
                <td><x-ui.badge>{{ __('projects::messages.type.'.$project['type']) }}</x-ui.badge></td>
                <td><x-ui.badge>{{ __('projects::messages.status.'.$project['status']) }}</x-ui.badge></td>
                <td><x-ui.progress class="w-32" :value="$project['progress']" /></td>
                <td>
                    <div class="flex flex-wrap gap-2">
                        @can('tasks.view')<x-ui.button size="sm" variant="secondary" :href="route('tasks.index', ['project' => $project['id']])">{{ __('app.tasks') }}</x-ui.button>@endcan
                        @can('tickets.view')<x-ui.button size="sm" variant="secondary" :href="route('tickets.index', ['project' => $project['id']])">{{ __('app.tickets') }}</x-ui.button>@endcan
                        @can('projects.update')<x-ui.button size="sm" variant="secondary" :href="route('projects.edit', $project['id'])">{{ __('app.edit') }}</x-ui.button>@endcan
                        @can('projects.delete')<form method="POST" action="{{ route('projects.destroy', $project['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<x-ui.button size="sm" variant="danger" type="submit">{{ __('app.delete') }}</x-ui.button></form>@endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-ui.empty-row colspan="6" />
        @endforelse
        </tbody>
    </x-ui.table>
@endsection
