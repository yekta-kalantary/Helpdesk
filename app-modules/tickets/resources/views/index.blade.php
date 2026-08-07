@extends('layouts.app')

@section('title', __('tickets::messages.tickets'))

@section('content')
    <x-ui.page-header :title="__('tickets::messages.tickets')">
        @can('tickets.create')<x-slot:actions><x-ui.button :href="route('tickets.create', ['project' => request('project')])">{{ __('tickets::messages.new_ticket') }}</x-ui.button></x-slot:actions>@endcan
    </x-ui.page-header>

    <x-ui.filter-bar>
        @if(request('project'))<input type="hidden" name="project" value="{{ request('project') }}">@endif
        <div class="min-w-0 flex-1"><x-ui.input name="q" :value="request('q')" :placeholder="__('tickets::messages.search_placeholder')" /></div>
        <x-ui.button variant="secondary" type="submit">{{ __('app.search') }}</x-ui.button>
    </x-ui.filter-bar>

    <x-ui.table>
        <thead><tr><th>{{ __('tickets::messages.subject') }}</th><th>{{ __('tickets::messages.customer') }}</th><th>{{ __('tickets::messages.project') }}</th><th>{{ __('tickets::messages.category') }}</th><th>{{ __('tickets::messages.priority') }}</th><th>{{ __('app.status') }}</th><th>{{ __('tickets::messages.assignee') }}</th></tr></thead>
        <tbody>
        @forelse($tickets as $ticket)
            <tr class="cursor-pointer transition hover:bg-slate-50" onclick="window.location='{{ route('tickets.show', $ticket['id']) }}'">
                <td class="font-semibold">#{{ $ticket['id'] }} — {{ $ticket['subject'] }}</td>
                <td>{{ $ticket['customer_name'] }}</td>
                <td>{{ $ticket['project_title'] ?: __('tickets::messages.no_project') }}</td>
                <td><x-ui.badge>{{ __('tickets::messages.category.'.$ticket['category']) }}</x-ui.badge></td>
                <td><x-ui.badge>{{ __('tickets::messages.priority.'.$ticket['priority']) }}</x-ui.badge></td>
                <td><x-ui.badge>{{ __('tickets::messages.status.'.$ticket['status']) }}</x-ui.badge></td>
                <td>{{ $ticket['assignee_name'] ?: __('tickets::messages.unassigned') }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="7" />
        @endforelse
        </tbody>
    </x-ui.table>
@endsection
