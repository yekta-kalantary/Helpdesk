@extends('layouts.app')

@section('title', $task['title'])

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="mb-2 flex flex-wrap gap-2"><span class="badge">{{ __('tasks::messages.status.'.$task['status']) }}</span><span class="badge">{{ __('tasks::messages.priority.'.$task['priority']) }}</span></div>
            <h1 class="text-2xl font-black">{{ $task['title'] }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $task['project_title'] }} · {{ $task['customer_name'] }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('tasks.update')<a class="btn-secondary" href="{{ route('tasks.edit', $task['id']) }}">{{ __('app.edit') }}</a>@endcan
            @can('tasks.delete')<form method="POST" action="{{ route('tasks.destroy', $task['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<button class="btn-danger" type="submit">{{ __('app.delete') }}</button></form>@endcan
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="card">
                <h2 class="mb-3 font-bold">{{ __('app.description') }}</h2>
                <div class="whitespace-pre-line text-sm leading-7 text-slate-700">{{ $task['description'] ?: '—' }}</div>
            </section>

            <section class="card">
                <h2 class="mb-4 font-bold">{{ __('tasks::messages.attachments') }}</h2>
                <div class="space-y-2">
                    @forelse($task['attachments'] as $attachment)
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 p-3">
                            <div><div class="text-sm font-medium">{{ $attachment['name'] }}</div><div class="text-xs text-slate-500">{{ number_format($attachment['size'] / 1024, 1) }} KB</div></div>
                            <div class="flex gap-2"><a class="btn-secondary" href="{{ route('tasks.attachments.download', [$task['id'], $attachment['id']]) }}">{{ __('tasks::messages.download') }}</a>@can('tasks.update')<form method="POST" action="{{ route('tasks.attachments.destroy', [$task['id'], $attachment['id']]) }}">@csrf @method('DELETE')<button class="btn-danger" type="submit">{{ __('app.delete') }}</button></form>@endcan</div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('app.no_records') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="card">
                <h2 class="mb-4 font-bold">{{ __('tasks::messages.comments') }}</h2>
                @can('tasks.comment')
                    <form class="mb-5 space-y-3" method="POST" action="{{ route('tasks.comments.store', $task['id']) }}">@csrf<textarea name="body" placeholder="{{ __('tasks::messages.comment_placeholder') }}" required></textarea><button class="btn-primary" type="submit">{{ __('tasks::messages.new_comment') }}</button></form>
                @endcan
                <div class="space-y-3">
                    @forelse($task['comments'] as $comment)
                        <article class="rounded-xl border border-slate-200 p-4"><div class="flex justify-between gap-3 text-xs text-slate-500"><span class="font-semibold text-slate-700">{{ $comment['user_name'] }}</span><span dir="ltr">{{ $comment['created_at']?->format('Y-m-d H:i') }}</span></div><div class="mt-3 whitespace-pre-line text-sm leading-7">{{ $comment['body'] }}</div></article>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('app.no_records') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            @can('tasks.update')
                <form class="card space-y-3" method="POST" action="{{ route('tasks.status.update', $task['id']) }}">@csrf @method('PATCH')<label for="status">{{ __('tasks::messages.change_status') }}</label><select id="status" name="status">@foreach(\Modules\Tasks\Domain\Enums\TaskStatus::cases() as $status)<option value="{{ $status->value }}" @selected($task['status'] === $status->value)>{{ __('tasks::messages.status.'.$status->value) }}</option>@endforeach</select><button class="btn-primary w-full" type="submit">{{ __('app.save') }}</button></form>
            @endcan
            <div class="card space-y-4 text-sm">
                <div><div class="text-xs text-slate-500">{{ __('tasks::messages.assignee') }}</div><div class="mt-1 font-medium">{{ $task['assignee_name'] ?: __('tasks::messages.unassigned') }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('tasks::messages.creator') }}</div><div class="mt-1 font-medium">{{ $task['creator_name'] }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('tasks::messages.due_at') }}</div><div class="mt-1" dir="ltr">{{ $task['due_at'] ? str_replace('T', ' ', $task['due_at']) : '—' }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('tasks::messages.estimated_minutes') }}</div><div class="mt-1">{{ $task['estimated_minutes'] ?? '—' }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('tasks::messages.spent_minutes') }}</div><div class="mt-1">{{ $task['spent_minutes'] ?? '—' }}</div></div>
            </div>
        </aside>
    </div>
@endsection
