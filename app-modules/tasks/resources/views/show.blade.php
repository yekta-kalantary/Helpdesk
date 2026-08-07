@extends('layouts.app')

@section('title', $task['title'])

@section('content')
    <x-ui.page-header :title="$task['title']" :subtitle="$task['project_title'].' · '.$task['customer_name']">
        <x-slot:actions>
            @can('tasks.update')<x-ui.button variant="secondary" :href="route('tasks.edit', $task['id'])">{{ __('app.edit') }}</x-ui.button>@endcan
            @can('tasks.delete')<form method="POST" action="{{ route('tasks.destroy', $task['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<x-ui.button variant="danger" type="submit">{{ __('app.delete') }}</x-ui.button></form>@endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-5 flex flex-wrap gap-2">
        <x-ui.badge>{{ __('tasks::messages.status.'.$task['status']) }}</x-ui.badge>
        <x-ui.badge>{{ __('tasks::messages.priority.'.$task['priority']) }}</x-ui.badge>
        @if(! $customerView && $task['is_customer_visible'])<x-ui.badge tone="info">{{ __('tasks::messages.customer_visible') }}</x-ui.badge>@endif
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-ui.card :title="__('app.description')">
                <div class="whitespace-pre-line text-sm leading-7 text-slate-700">{{ $task['description'] ?: '—' }}</div>
            </x-ui.card>

            <x-ui.card :title="__('tasks::messages.attachments')">
                <div class="space-y-2">
                    @forelse($task['attachments'] as $attachment)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3">
                            <div><div class="text-sm font-semibold text-slate-800">{{ $attachment['name'] }}</div><div class="mt-1 text-xs text-slate-500">{{ number_format($attachment['size'] / 1024, 1) }} KB</div></div>
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button size="sm" variant="secondary" :href="route('tasks.attachments.download', [$task['id'], $attachment['id']])">{{ __('tasks::messages.download') }}</x-ui.button>
                                @can('tasks.update')<form method="POST" action="{{ route('tasks.attachments.destroy', [$task['id'], $attachment['id']]) }}">@csrf @method('DELETE')<x-ui.button size="sm" variant="danger" type="submit">{{ __('app.delete') }}</x-ui.button></form>@endcan
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state :description="__('app.no_records')" />
                    @endforelse
                </div>
            </x-ui.card>

            @if(! $customerView)
                <x-ui.card :title="__('tasks::messages.comments')">
                    @can('tasks.comment')
                        <form class="mb-5 space-y-3" method="POST" action="{{ route('tasks.comments.store', $task['id']) }}">
                            @csrf
                            <x-ui.textarea name="body" :placeholder="__('tasks::messages.comment_placeholder')" required />
                            <x-ui.button type="submit">{{ __('tasks::messages.new_comment') }}</x-ui.button>
                        </form>
                    @endcan

                    <div class="space-y-3">
                        @forelse($task['comments'] as $comment)
                            <article class="rounded-xl border border-slate-200 p-4">
                                <div class="flex justify-between gap-3 text-xs text-slate-500"><span class="font-semibold text-slate-700">{{ $comment['user_name'] }}</span><span dir="ltr">{{ $comment['created_at']?->format('Y-m-d H:i') }}</span></div>
                                <div class="mt-3 whitespace-pre-line text-sm leading-7">{{ $comment['body'] }}</div>
                            </article>
                        @empty
                            <x-ui.empty-state :description="__('app.no_records')" />
                        @endforelse
                    </div>
                </x-ui.card>
            @endif
        </div>

        <aside class="space-y-4">
            @can('tasks.update')
                <form method="POST" action="{{ route('tasks.status.update', $task['id']) }}">
                    @csrf @method('PATCH')
                    <x-ui.card :title="__('tasks::messages.change_status')">
                        <div class="space-y-4">
                            <x-ui.select name="status" :label="__('tasks::messages.change_status')">
                                @foreach(\Modules\Tasks\Domain\Enums\TaskStatus::cases() as $status)<option value="{{ $status->value }}" @selected($task['status'] === $status->value)>{{ __('tasks::messages.status.'.$status->value) }}</option>@endforeach
                            </x-ui.select>
                            <x-ui.button class="w-full" type="submit">{{ __('app.save') }}</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            @endcan

            <x-ui.card>
                <div class="space-y-4">
                    <x-ui.meta-item :label="__('tasks::messages.assignee')">{{ $task['assignee_name'] ?: __('tasks::messages.unassigned') }}</x-ui.meta-item>
                    <x-ui.meta-item :label="__('tasks::messages.due_at')"><span dir="ltr">{{ $task['due_at'] ? str_replace('T', ' ', $task['due_at']) : '—' }}</span></x-ui.meta-item>
                    @if(! $customerView)
                        <x-ui.meta-item :label="__('tasks::messages.creator')">{{ $task['creator_name'] }}</x-ui.meta-item>
                        <x-ui.meta-item :label="__('tasks::messages.estimated_minutes')">{{ $task['estimated_minutes'] ?? '—' }}</x-ui.meta-item>
                        <x-ui.meta-item :label="__('tasks::messages.spent_minutes')">{{ $task['spent_minutes'] ?? '—' }}</x-ui.meta-item>
                    @endif
                </div>
            </x-ui.card>
        </aside>
    </div>
@endsection
