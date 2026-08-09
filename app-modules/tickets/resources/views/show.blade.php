<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="'#'.$ticket['id'].' — '.$ticket['subject']" :subtitle="$ticket['customer_name'].($ticket['project_title'] ? ' · '.$ticket['project_title'] : '')">
        <x-slot:actions>
            @can('tickets.delete')
                <x-ui.button variant="danger" icon="fa-trash" wire:click="deleteTicket" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="deleteTicket">{{ __('app.delete') }}</x-ui.button>
            @endcan
        </x-slot:actions>
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
                        <article class="rounded-xl border border-slate-200 p-4" wire:key="ticket-message-{{ $message['id'] }}">
                            <div class="flex flex-wrap justify-between gap-2 text-xs text-slate-500">
                                <span class="font-semibold text-slate-800">{{ $message['user_name'] }}</span>
                                <span dir="ltr">{{ $message['created_at']?->format('Y-m-d H:i') }}</span>
                            </div>
                            <div class="mt-3 whitespace-pre-line text-sm leading-7">{{ $message['body'] }}</div>

                            @if($message['attachments'])
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($message['attachments'] as $attachment)
                                        <x-ui.button size="sm" variant="secondary" icon="fa-download" :href="route('tickets.attachments.download', [$ticket['id'], $message['id'], $attachment['id']])">{{ __('tickets::messages.download') }}: {{ $attachment['name'] }}</x-ui.button>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </x-ui.card>

            @can('tickets.reply')
                <form wire:submit="reply">
                    <x-ui.card :title="__('tickets::messages.reply')">
                        <div class="space-y-4">
                            <x-ui.textarea name="replyBody" :label="__('tickets::messages.reply')" :value="$replyBody" wire:model="replyBody" required />
                            <x-ui.input name="replyAttachments" :label="__('tickets::messages.attachments')" type="file" wire:model="replyAttachments" multiple />
                            @error('replyAttachments.*')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            <x-ui.button type="submit" icon="fa-paper-plane" wire:loading.attr="disabled" wire:target="reply,replyAttachments">
                                <span wire:loading.remove wire:target="reply">{{ __('tickets::messages.reply') }}</span>
                                <span wire:loading wire:target="reply">{{ __('app.loading') }}</span>
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            @endcan
        </div>

        <aside class="space-y-4">
            @can('tickets.manage')
                <form wire:submit="manage">
                    <x-ui.card :title="__('tickets::messages.manage_ticket')">
                        <div class="space-y-4">
                            <x-ui.select name="status" :label="__('app.status')" wire:model="status">
                                @foreach($statuses as $statusItem)
                                    <option value="{{ $statusItem->value }}">{{ __('tickets::messages.status.'.$statusItem->value) }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.select name="assigned_to" :label="__('tickets::messages.assignee')" wire:model.number="assigned_to">
                                <option value="">{{ __('tickets::messages.unassigned') }}</option>
                                @foreach($options['members'] as $member)
                                    <option value="{{ $member['id'] }}">{{ $member['name'] }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.button class="w-full" type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="manage">
                                <span wire:loading.remove wire:target="manage">{{ __('app.save') }}</span>
                                <span wire:loading wire:target="manage">{{ __('app.loading') }}</span>
                            </x-ui.button>
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
</div>
