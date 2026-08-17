<div>
    <x-ui.page-header title="اعلان‌ها" subtitle="به‌روزرسانی‌های پروژه‌ها و کارهایی که به توجه شما نیاز دارند.">
        <x-slot:actions>
            <div class="flex items-center gap-2 text-body-sm text-text-muted">
                <span>خوانده‌نشده</span>
                <x-ui.badge tone="info" aria-label="{{ $unreadCount }} اعلان خوانده‌نشده">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</x-ui.badge>
            </div>
            <x-ui.button variant="secondary" wire:click="markAllRead" wire:loading.attr="disabled" wire:target="markAllRead">
                <span wire:loading.remove wire:target="markAllRead">خواندن همه</span>
                <span wire:loading wire:target="markAllRead">در حال به‌روزرسانی...</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="divide-y divide-workspace-divider border-y border-workspace-divider">
        @php($notificationDate = null)
        @forelse($notifications as $notification)
            @php($currentDate = $notification->created_at->translatedFormat('l، j F'))
            @if($notificationDate !== $currentDate)
                @php($notificationDate = $currentDate)
                <h2 class="border-b border-border px-1 pb-2 pt-5 text-caption font-black tracking-wide text-text-muted first:pt-0">{{ $currentDate }}</h2>
            @endif
            <button type="button" aria-labelledby="notification-{{ $notification->id }}-title" aria-describedby="notification-{{ $notification->id }}-details notification-{{ $notification->id }}-status" wire:click="open('{{ $notification->id }}')" wire:loading.attr="disabled" wire:target="open('{{ $notification->id }}')" @class([
                'block min-h-11 w-full px-1 py-4 text-right transition hover:bg-surface-muted sm:px-3',
                'bg-surface' => $notification->read_at,
                'bg-info-surface bg-workspace-info-surface' => !$notification->read_at,
            ])>
                <div class="flex items-start gap-3">
                    <span @class([
                        'mt-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
                        'bg-surface-muted text-text-muted' => $notification->read_at,
                        'bg-info text-surface' => !$notification->read_at,
                    ])><i class="fa-light {{ $notification->read_at ? 'fa-envelope-open' : 'fa-bell' }}" aria-hidden="true"></i></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div id="notification-{{ $notification->id }}-title" @class(['font-bold text-text', 'font-black' => !$notification->read_at])>{{ $notification->data['title'] ?? 'اعلان' }}</div>
                            @if(!$notification->read_at)
                                <x-ui.badge tone="info">خوانده‌نشده</x-ui.badge>
                            @endif
                        </div>
                        <div id="notification-{{ $notification->id }}-details" class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-body-sm leading-6 text-text-muted">
                            <span>{{ $notification->data['body'] ?? '' }}</span>
                            @if($notification->data['source'] ?? $notification->data['resource_type'] ?? null)
                                <span class="text-text-muted" aria-hidden="true">·</span>
                                <span>منبع: {{ $notification->data['source'] ?? $notification->data['resource_type'] }}</span>
                            @endif
                            @if($notification->data['context'] ?? $notification->data['resource_name'] ?? null)
                                <span class="text-text-muted" aria-hidden="true">·</span>
                                <span>زمینه: {{ $notification->data['context'] ?? $notification->data['resource_name'] }}</span>
                            @endif
                            <time class="ui-metadata" datetime="{{ $notification->created_at->toIso8601String() }}"><x-ui.date :value="$notification->created_at" /></time>
                            @if($notification->data['url'] ?? null)<span class="ui-metadata">مقصد: مشاهده</span>@endif
                        </div>
                        <span id="notification-{{ $notification->id }}-status" class="sr-only">{{ $notification->read_at ? 'خوانده شده' : 'خوانده‌نشده' }}؛ {{ $notification->data['url'] ?? null ? 'مقصد مشاهده در دسترس است؛ ' : '' }}برای باز کردن انتخاب کنید.</span>
                    </div>
                </div>
            </button>
        @empty
            <x-ui.empty-state title="صندوق اعلان‌ها خالی است" description="وقتی در پروژه‌ها یا کارهای شما تغییری ایجاد شود، اعلان آن را اینجا می‌بینید." />
        @endforelse
    </div>

    <div class="mt-5">{{ $notifications->links() }}</div>
</div>
