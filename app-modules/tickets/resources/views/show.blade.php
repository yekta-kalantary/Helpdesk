@extends('layouts.app')

@section('title', $ticket['subject'])

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="mb-2 flex flex-wrap gap-2"><span class="badge">{{ __('tickets::messages.status.'.$ticket['status']) }}</span><span class="badge">{{ __('tickets::messages.priority.'.$ticket['priority']) }}</span><span class="badge">{{ __('tickets::messages.category.'.$ticket['category']) }}</span></div>
            <h1 class="text-2xl font-black">#{{ $ticket['id'] }} — {{ $ticket['subject'] }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $ticket['customer_name'] }} @if($ticket['project_title']) · {{ $ticket['project_title'] }} @endif</p>
        </div>
        @can('tickets.delete')<form method="POST" action="{{ route('tickets.destroy', $ticket['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<button class="btn-danger" type="submit">{{ __('app.delete') }}</button></form>@endcan
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <section class="card">
                <h2 class="mb-4 font-bold">{{ __('tickets::messages.conversation') }}</h2>
                <div class="space-y-4">
                    @foreach($ticket['messages'] as $message)
                        <article class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-wrap justify-between gap-2 text-xs text-slate-500"><span class="font-semibold text-slate-800">{{ $message['user_name'] }}</span><span dir="ltr">{{ $message['created_at']?->format('Y-m-d H:i') }}</span></div>
                            <div class="mt-3 whitespace-pre-line text-sm leading-7">{{ $message['body'] }}</div>
                            @if($message['attachments'])
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($message['attachments'] as $attachment)
                                        <a class="btn-secondary" href="{{ route('tickets.attachments.download', [$ticket['id'], $message['id'], $attachment['id']]) }}">{{ __('tickets::messages.download') }}: {{ $attachment['name'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            @can('tickets.reply')
                <form class="card space-y-4" method="POST" enctype="multipart/form-data" action="{{ route('tickets.reply', $ticket['id']) }}">
                    @csrf
                    <div><label for="body">{{ __('tickets::messages.reply') }}</label><textarea id="body" name="body" required>{{ old('body') }}</textarea></div>
                    <div><label for="attachments">{{ __('tickets::messages.attachments') }}</label><input id="attachments" name="attachments[]" type="file" multiple></div>
                    <button class="btn-primary" type="submit">{{ __('tickets::messages.reply') }}</button>
                </form>
            @endcan
        </div>

        <aside class="space-y-4">
            @can('tickets.manage')
                <form class="card space-y-4" method="POST" action="{{ route('tickets.manage', $ticket['id']) }}">
                    @csrf @method('PATCH')
                    <h2 class="font-bold">{{ __('tickets::messages.manage_ticket') }}</h2>
                    <div><label for="status">{{ __('app.status') }}</label><select id="status" name="status">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected($ticket['status'] === $status->value)>{{ __('tickets::messages.status.'.$status->value) }}</option>@endforeach</select></div>
                    <div><label for="assigned_to">{{ __('tickets::messages.assignee') }}</label><select id="assigned_to" name="assigned_to"><option value="">{{ __('tickets::messages.unassigned') }}</option>@foreach($options['members'] as $member)<option value="{{ $member['id'] }}" @selected((string) $ticket['assigned_to'] === (string) $member['id'])>{{ $member['name'] }}</option>@endforeach</select></div>
                    <button class="btn-primary w-full" type="submit">{{ __('app.save') }}</button>
                </form>
            @endcan

            <div class="card space-y-4 text-sm">
                <div><div class="text-xs text-slate-500">{{ __('tickets::messages.customer') }}</div><div class="mt-1 font-medium">{{ $ticket['customer_name'] }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('tickets::messages.project') }}</div><div class="mt-1">{{ $ticket['project_title'] ?: __('tickets::messages.no_project') }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('tickets::messages.assignee') }}</div><div class="mt-1">{{ $ticket['assignee_name'] ?: __('tickets::messages.unassigned') }}</div></div>
            </div>
        </aside>
    </div>
@endsection
