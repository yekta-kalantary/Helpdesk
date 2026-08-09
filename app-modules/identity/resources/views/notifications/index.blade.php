<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('identity::notifications.title')">
        <x-slot:actions>
            @if(auth()->user()->unreadNotifications()->exists())
                <x-ui.button variant="secondary" icon="fa-check-double" wire:click="markAllRead" wire:loading.attr="disabled" wire:target="markAllRead">
                    <span wire:loading.remove wire:target="markAllRead">{{ __('identity::notifications.mark_all_read') }}</span>
                    <span wire:loading wire:target="markAllRead">{{ __('app.loading') }}</span>
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            @php($messageKey = $notification->data['message_key'] ?? null)

            <x-ui.card wire:key="notification-{{ $notification->id }}" class="{{ $notification->read_at ? '' : 'ring-1 ring-slate-300' }}">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <x-ui.badge :tone="$notification->read_at ? 'neutral' : 'info'">{{ $notification->read_at ? __('identity::notifications.read') : __('identity::notifications.unread') }}</x-ui.badge>
                            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500" dir="ltr">
                                <i class="fa-light fa-clock" aria-hidden="true"></i>
                                {{ $notification->created_at?->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 font-semibold text-slate-900">
                            <i class="fa-light fa-bell text-slate-400" aria-hidden="true"></i>
                            <span>{{ $messageKey ? __($messageKey) : __('identity::notifications.title') }}</span>
                        </div>
                        @if(isset($notification->data['task_title']))
                            <div class="mt-1 text-sm text-slate-500">{{ $notification->data['task_title'] }}</div>
                        @endif
                        @if(isset($notification->data['subject']))
                            <div class="mt-1 text-sm text-slate-500">{{ $notification->data['subject'] }}</div>
                        @endif
                    </div>

                    <x-ui.button icon="fa-eye" wire:click="open('{{ $notification->id }}')" wire:loading.attr="disabled" wire:target="open('{{ $notification->id }}')">{{ __('identity::notifications.open') }}</x-ui.button>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state :description="__('identity::notifications.empty')" />
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
</div>
