@extends('layouts.app')

@section('title', __('identity::notifications.title'))

@section('content')
    <x-ui.page-header :title="__('identity::notifications.title')">
        <x-slot:actions>
            @if(auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-ui.button variant="secondary" type="submit">{{ __('identity::notifications.mark_all_read') }}</x-ui.button>
                </form>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            @php($messageKey = $notification->data['message_key'] ?? null)

            <x-ui.card class="{{ $notification->read_at ? '' : 'ring-1 ring-slate-300' }}">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <x-ui.badge :tone="$notification->read_at ? 'neutral' : 'info'">{{ $notification->read_at ? __('identity::notifications.read') : __('identity::notifications.unread') }}</x-ui.badge>
                            <span class="text-xs text-slate-500" dir="ltr">{{ $notification->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="font-semibold text-slate-900">{{ $messageKey ? __($messageKey) : __('identity::notifications.title') }}</div>

                        @if(isset($notification->data['task_title']))
                            <div class="mt-1 text-sm text-slate-500">{{ $notification->data['task_title'] }}</div>
                        @endif

                        @if(isset($notification->data['subject']))
                            <div class="mt-1 text-sm text-slate-500">{{ $notification->data['subject'] }}</div>
                        @endif
                    </div>

                    <x-ui.button :href="route('notifications.open', $notification->id)">{{ __('identity::notifications.open') }}</x-ui.button>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state :description="__('identity::notifications.empty')" />
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
@endsection
