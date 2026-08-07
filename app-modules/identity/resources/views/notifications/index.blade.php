@extends('layouts.app')

@section('title', __('identity::notifications.title'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black">{{ __('identity::notifications.title') }}</h1>
        @if(auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn-secondary" type="submit">{{ __('identity::notifications.mark_all_read') }}</button></form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            @php($messageKey = $notification->data['message_key'] ?? null)
            <article class="card flex flex-wrap items-center justify-between gap-4 {{ $notification->read_at ? '' : 'ring-1 ring-slate-300' }}">
                <div>
                    <div class="mb-1 flex items-center gap-2">
                        <span class="badge">{{ $notification->read_at ? __('identity::notifications.read') : __('identity::notifications.unread') }}</span>
                        <span class="text-xs text-slate-500" dir="ltr">{{ $notification->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="font-semibold">{{ $messageKey ? __($messageKey) : __('identity::notifications.title') }}</div>
                    @if(isset($notification->data['task_title']))<div class="mt-1 text-sm text-slate-500">{{ $notification->data['task_title'] }}</div>@endif
                    @if(isset($notification->data['subject']))<div class="mt-1 text-sm text-slate-500">{{ $notification->data['subject'] }}</div>@endif
                </div>
                <a class="btn-primary" href="{{ route('notifications.open', $notification->id) }}">{{ __('identity::notifications.open') }}</a>
            </article>
        @empty
            <div class="card text-sm text-slate-500">{{ __('identity::notifications.empty') }}</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
@endsection
