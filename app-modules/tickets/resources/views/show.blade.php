@extends('layouts.app')

@section('title', $ticket['subject'])

@section('content')
    <x-ui.page-header :title="'#'.$ticket['id'].' — '.$ticket['subject']" :subtitle="$ticket['customer_name'].($ticket['project_title'] ? ' · '.$ticket['project_title'] : '')">
        @can('tickets.delete')
            <x-slot:actions><form method="POST" action="{{ route('tickets.destroy', $ticket['id']) }}" onsubmit="return confirm(@js(__('app.confirm_delete')))" >@csrf @method('DELETE')<x-ui.button variant="danger" type="submit">{{ __('app.delete') }}</x-ui.button></form></x-slot:actions>
        @endcan
    </x-ui.page-header>

    <div class="mb-5 flex flex-wrap gap-2">
        <x-ui.badge>{{ __('tickets::messages.status.'.$ticket['status']) }}</x-ui.badge>
        <x-ui.badge>{{ __('tickets::messages.priority.'.$ticket['priority']) }}</x-ui.badge>
        <x-ui.badge>{{ __('tickets::messages.category.'.$ticket['category']) }}</x-ui.badge>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <x-ui.card :title="__('tickets::messages.conversation')">
                <div class="space-y-4">
                    @foreach($ticket['messages'] as $message)
                        <article class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-wrap justify-between gap-2 text-xs text-slate-500"><span class="font-semibold text-slate-800">{{ $message['user_name'] }}</span><span dir="ltr">{{ $message['created_at']?->format('Y-m-d H:i') }}</span></div>
                            <div class="mt-3 whitespace-pre-line text-sm leading-7">{{ $message['body'] }}</div>
                            @if($message['attachments'])
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($message['attachments'] as $attachment)
                                        <x-ui.button size="sm" variant="secondary" :href="route('tickets.attachments.download', [$ticket['id'], $message['id'], $attachment['id']])">{{ __('tickets::messages.download') }}: {{ $attachment['name'] }}</x-ui.button>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </x-ui.card>

            @can('tickets.reply')
                <form method="POST" enctype="multipart/form-data" action="{{ route('tickets.reply', $ticket['id']) }}">
                    @csrf
                    <x-ui.card :title="__('tickets::messages.reply')">
                        <div class="space-y-4">
                            <x-ui.textarea name="body" :label="__('tickets::messages.reply')" :value="old('body')" required />
                            <x-ui.input name="attachments[]" :label="__('tickets::messages.attachments')" type="file" multiple />
                            <x-ui.button type="submit">{{ __('tickets::messages.reply') }}</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            @endcan
        </div>

        <aside class="space-y-4">
            @can('tickets.manage')
                <form method="POST" action="{{ route('tickets.manage', $ticket['id']) }}">
                    @csrf @method('PATCH')
                    <x-ui.card :title="__('tickets::messages.manage_ticket')">
                        <div class="space-y-4">
                            <x-ui.select name="status" :label="__('app.status')">
                                @foreach($statuses as $status)<option value="{{ $status->value }}" @selected($ticket['status'] === $status->value)>{{ __('tickets::messages.status.'.$status->value) }}</option>@endforeach
                            </x-ui.select>
                            <x-ui.select name="assigned_to" :label="__('tickets::messages.assignee')">
                                <option value="">{{ __('tickets::messages.unassigned') }}</option>
                                @foreach($options['members'] as $member)<option value="{{ $member['id'] }}" @selected((string) $ticket['assigned_to'] === (string) $member['id'])>{{ $member['name'] }}</option>@endforeach
                            </x-ui.select>
                            <x-ui.button class="w-full" type="submit">{{ __('app.save') }}</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            @endcan

            <x-ui.card>
                <div class="space-y-4">
                    <x-ui.meta-item :label="__('tickets::messages.customer')">{{ $ticket['customer_name'] }}</x-ui.meta-item>
                    <x-ui.meta-item :label="__('tickets::messages.project')">{{ $ticket['project_title'] ?: __('tickets::messages.no_project') }}</x-ui.meta-item>
                    <x-ui.meta-item :label="__('tickets::messages.assignee')">{{ $ticket['assignee_name'] ?: __('tickets::messages.unassigned') }}</x-ui.meta-item>
                </div>
            </x-ui.card>
        </aside>
    </div>
@endsection
