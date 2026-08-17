<div>
    <x-ui.page-header title="اعلان‌ها" subtitle="به‌روزرسانی‌های پروژه‌ها و کارهایی که به توجه شما نیاز دارند.">
        <x-slot:actions>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>خوانده‌نشده</span>
                <span class="inline-flex min-w-7 items-center justify-center rounded-full bg-slate-950 px-2 py-1 text-xs font-black text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            </div>
            <x-ui.button variant="secondary" wire:click="markAllRead" wire:loading.attr="disabled" wire:target="markAllRead">خواندن همه</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="divide-y divide-workspace-divider border-y border-workspace-divider">
        @php($notificationDate = null)
        @forelse($notifications as $notification)
            @php($currentDate = $notification->created_at->translatedFormat('l، j F'))
            @if($notificationDate !== $currentDate)
                @php($notificationDate = $currentDate)
                <h2 class="border-b border-workspace-divider px-1 pb-2 pt-5 text-xs font-black tracking-wide text-workspace-muted first:pt-0">{{ $currentDate }}</h2>
            @endif
            <button type="button" aria-label="{{ $notification->data['title'] ?? 'اعلان' }}" wire:click="open('{{ $notification->id }}')" wire:loading.attr="disabled" wire:target="open('{{ $notification->id }}')" @class([
                'block min-h-11 w-full px-1 py-4 text-right transition hover:bg-workspace-neutral-surface sm:px-3',
                'bg-workspace-surface' => $notification->read_at,
                'bg-workspace-info-surface' => !$notification->read_at,
            ])>
                <div class="flex items-start gap-3">
                    <span @class([
                        'mt-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
                        'bg-slate-100 text-slate-400' => $notification->read_at,
                        'bg-workspace-teal text-white' => !$notification->read_at,
                    ])><i class="fa-light fa-bell" aria-hidden="true"></i></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                        <div @class(['font-bold text-workspace-text', 'font-black' => !$notification->read_at])>{{ $notification->data['title'] ?? 'اعلان' }}</div>
                            @if(!$notification->read_at)<x-ui.badge tone="info">خوانده‌نشده</x-ui.badge>@endif
                        </div>
                        <div class="mt-1 text-sm leading-6 text-workspace-muted">{{ $notification->data['body'] ?? '' }}</div>
                        <time class="mt-2 block text-xs text-workspace-muted"><x-ui.date :value="$notification->created_at" datetime /></time>
                    </div>
                </div>
            </button>
        @empty
            <x-ui.empty-state title="صندوق اعلان‌ها خالی است" description="وقتی در پروژه‌ها یا کارهای شما تغییری ایجاد شود، اعلان آن را اینجا می‌بینید." />
        @endforelse
    </div>

    <div class="mt-5">{{ $notifications->links() }}</div>
</div>
