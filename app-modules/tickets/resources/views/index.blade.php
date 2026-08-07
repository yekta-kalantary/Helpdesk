@extends('layouts.app')

@section('title', __('tickets::messages.tickets'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black">{{ __('tickets::messages.tickets') }}</h1>
        @can('tickets.create')<a class="btn-primary" href="{{ route('tickets.create', ['project' => request('project')]) }}">{{ __('tickets::messages.new_ticket') }}</a>@endcan
    </div>

    <form class="mb-4 flex gap-2" method="GET">
        @if(request('project'))<input type="hidden" name="project" value="{{ request('project') }}">@endif
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('tickets::messages.search_placeholder') }}">
        <button class="btn-secondary" type="submit">{{ __('app.search') }}</button>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>{{ __('tickets::messages.subject') }}</th><th>{{ __('tickets::messages.customer') }}</th><th>{{ __('tickets::messages.project') }}</th><th>{{ __('tickets::messages.category') }}</th><th>{{ __('tickets::messages.priority') }}</th><th>{{ __('app.status') }}</th><th>{{ __('tickets::messages.assignee') }}</th></tr></thead>
            <tbody>
            @forelse($tickets as $ticket)
                <tr class="cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket['id']) }}'">
                    <td class="font-semibold">#{{ $ticket['id'] }} — {{ $ticket['subject'] }}</td>
                    <td>{{ $ticket['customer_name'] }}</td>
                    <td>{{ $ticket['project_title'] ?: __('tickets::messages.no_project') }}</td>
                    <td><span class="badge">{{ __('tickets::messages.category.'.$ticket['category']) }}</span></td>
                    <td><span class="badge">{{ __('tickets::messages.priority.'.$ticket['priority']) }}</span></td>
                    <td><span class="badge">{{ __('tickets::messages.status.'.$ticket['status']) }}</span></td>
                    <td>{{ $ticket['assignee_name'] ?: __('tickets::messages.unassigned') }}</td>
                </tr>
            @empty
                <tr><td colspan="7">{{ __('app.no_records') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
