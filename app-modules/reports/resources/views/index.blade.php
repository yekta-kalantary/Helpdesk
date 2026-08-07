<div wire:poll.60s>
    <x-ui.page-header :title="__('reports::messages.reports')" />

    <div class="space-y-8" wire:loading.class="opacity-60">
        <section>
            <h2 class="mb-3 text-lg font-bold text-slate-950">{{ __('reports::messages.customers') }}</h2>
            <x-ui.table>
                <thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('reports::messages.company') }}</th><th>{{ __('app.status') }}</th><th>{{ __('reports::messages.projects_count') }}</th><th>{{ __('reports::messages.open_tickets') }}</th></tr></thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr wire:key="report-customer-{{ $customer->id }}"><td class="font-semibold">{{ $customer->name }}</td><td>{{ $customer->company ?: '—' }}</td><td><x-ui.badge>{{ __('customers::messages.status.'.$customer->status) }}</x-ui.badge></td><td>{{ $customer->projects_count }}</td><td>{{ $customer->open_tickets_count }}</td></tr>
                @empty<x-ui.empty-row colspan="5" />@endforelse
                </tbody>
            </x-ui.table>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-bold text-slate-950">{{ __('reports::messages.projects') }}</h2>
            <x-ui.table>
                <thead><tr><th>{{ __('app.title') }}</th><th>{{ __('projects::messages.customer') }}</th><th>{{ __('app.status') }}</th><th>{{ __('reports::messages.tasks_count') }}</th><th>{{ __('reports::messages.done_tasks') }}</th><th>{{ __('reports::messages.overdue_tasks') }}</th><th>{{ __('reports::messages.progress') }}</th></tr></thead>
                <tbody>
                @forelse($projects as $project)
                    @php($progress = $project->tasks_count > 0 ? (int) round(($project->done_tasks_count / $project->tasks_count) * 100) : 0)
                    <tr wire:key="report-project-{{ $project->id }}"><td class="font-semibold">{{ $project->title }}</td><td>{{ $project->customer_name }}</td><td><x-ui.badge>{{ __('projects::messages.status.'.$project->status) }}</x-ui.badge></td><td>{{ $project->tasks_count }}</td><td>{{ $project->done_tasks_count }}</td><td>{{ $project->overdue_tasks_count }}</td><td><x-ui.progress class="w-28" :value="$progress" /></td></tr>
                @empty<x-ui.empty-row colspan="7" />@endforelse
                </tbody>
            </x-ui.table>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-bold text-slate-950">{{ __('reports::messages.team') }}</h2>
            <x-ui.table>
                <thead><tr><th>{{ __('app.name_label') }}</th><th>{{ __('app.email') }}</th><th>{{ __('reports::messages.assigned_tasks') }}</th><th>{{ __('reports::messages.done_tasks') }}</th><th>{{ __('reports::messages.overdue_tasks') }}</th></tr></thead>
                <tbody>
                @forelse($team as $member)
                    <tr wire:key="report-team-{{ $member->id }}"><td class="font-semibold">{{ $member->name }}</td><td dir="ltr" class="text-right">{{ $member->email }}</td><td>{{ $member->assigned_tasks_count }}</td><td>{{ $member->done_tasks_count }}</td><td>{{ $member->overdue_tasks_count }}</td></tr>
                @empty<x-ui.empty-row colspan="5" />@endforelse
                </tbody>
            </x-ui.table>
        </section>
    </div>
</div>
