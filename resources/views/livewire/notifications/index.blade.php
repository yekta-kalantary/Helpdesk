<div>
    <x-ui.page-header title="اعلان‌ها">
        <x-slot:actions>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>خوانده‌نشده</span>
                <span class="inline-flex min-w-7 items-center justify-center rounded-full bg-slate-950 px-2 py-1 text-xs font-black text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            </div>
            <x-ui.button variant="secondary" wire:click="markAllRead" wire:loading.attr="disabled" wire:target="markAllRead">خواندن همه</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <button type="button" wire:click="open('{{ $notification->id }}')" wire:loading.attr="disabled" wire:target="open('{{ $notification->id }}')" @class([
                'block w-full rounded-2xl border p-4 text-right transition hover:bg-slate-50',
                'border-slate-300 bg-white' => $notification->read_at,
                'border-slate-950 bg-slate-50' => !$notification->read_at,
            ])>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="font-black text-slate-950">{{ $notification->data['title'] ?? 'اعلان' }}</div>
                        <div class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->data['body'] ?? '' }}</div>
                    </div>
                    <time class="shrink-0 text-xs text-slate-500"><x-ui.date :value="$notification->created_at" datetime /></time>
                </div>
            </button>
        @empty
            <x-ui.card><p class="text-sm text-slate-500">اعلانی وجود ندارد.</p></x-ui.card>
        @endforelse
    </div>

    <div class="mt-5">{{ $notifications->links() }}</div>
</div>
