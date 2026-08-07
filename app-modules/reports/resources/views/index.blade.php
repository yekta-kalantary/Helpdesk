@extends('layouts.app')

@section('title', __('reports::messages.reports'))

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-black">{{ __('reports::messages.reports') }}</h1></div>

    <div class="space-y-8">
        <section>
            <h2 class="mb-3 text-lg font-bold">{{ __('reports::messages.customers') }}</h2>
            <div class="table-wrap"><table class="table"><thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('reports::messages.company') }}</th><th>{{ __('app.status') }}</th><th>{{ __('reports::messages.projects_count') }}</th><th>{{ __('reports::messages.open_tickets') }}</th></tr></thead><tbody>
            @forelse($customers as $customer)<tr><td class="font-semibold">{{ $customer->name }}</td><td>{{ $customer->company ?: '—' }}</td><td><span class="badge">{{ __('customers::messages.status.'.$customer->status) }}</span></td><td>{{ $customer->projects_count }}</td><td>{{ $customer->open_tickets_count }}</td></tr>@empty<tr><td colspan="5">{{ __('app.no_records') }}</td></tr>@endforelse
            </tbody></table></div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-bold">{{ __('reports::messages.projects') }}</h2>
            <div class="table-wrap"><table class="table"><thead><tr><th>{{ __('app.title') }}</th><th>{{ __('projects::messages.customer') }}</th><th>{{ __('app.status') }}</th><th>{{ __('reports::messages.tasks_count') }}</th><th>{{ __('reports::messages.done_tasks') }}</th><th>{{ __('reports::messages.overdue_tasks') }}</th><th>{{ __('reports::messages.progress') }}</th></tr></thead><tbody>
            @forelse($projects as $project)
                @php($progress = $project->tasks_count > 0 ? (int) round(($project->done_tasks_count / $project->tasks_count) * 100) : 0)
                <tr><td class="font-semibold">{{ $project->title }}</td><td>{{ $project->customer_name }}</td><td><span class="badge">{{ __('projects::messages.status.'.$project->status) }}</span></td><td>{{ $project->tasks_count }}</td><td>{{ $project->done_tasks_count }}</td><td>{{ $project->overdue_tasks_count }}</td><td>{{ $progress }}%</td></tr>
            @empty<tr><td colspan="7">{{ __('app.no_records') }}</td></tr>@endforelse
            </tbody></table></div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-bold">{{ __('reports::messages.team') }}</h2>
            <div class="table-wrap"><table class="table"><thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('app.email') }}</th><th>{{ __('reports::messages.assigned_tasks') }}</th><th>{{ __('reports::messages.done_tasks') }}</th><th>{{ __('reports::messages.overdue_tasks') }}</th></tr></thead><tbody>
            @forelse($team as $member)<tr><td class="font-semibold">{{ $member->name }}</td><td dir="ltr" class="text-right">{{ $member->email }}</td><td>{{ $member->assigned_tasks_count }}</td><td>{{ $member->done_tasks_count }}</td><td>{{ $member->overdue_tasks_count }}</td></tr>@empty<tr><td colspan="5">{{ __('app.no_records') }}</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
@endsection
