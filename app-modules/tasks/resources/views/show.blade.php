<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header
        :title="$task['title']"
        :subtitle="collect([$task['project_title'], $task['contact_name']])->filter()->implode(' · ')"
    >
        <x-slot:actions>
            @can('tasks.update')
                <x-ui.button variant="secondary" :href="route('tasks.edit', $task['id'])" icon="fa-pen-to-square" wire:navigate>{{ __('app.edit') }}</x-ui.button>
            @endcan
            @can('tasks.delete')
                <x-ui.button variant="danger" icon="fa-trash" wire:click="deleteTask" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="deleteTask">{{ __('app.delete') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-5 flex flex-wrap gap-2">
        <x-ui.badge>{{ __('tasks::messages.status.'.$task['status']) }}</x-ui.badge>
        <x-ui.badge>{{ __('tasks::messages.priority.'.$task['priority']) }}</x-ui.badge>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-ui.card :title="__('app.description')">
                <div class="whitespace-pre-line text-sm leading-7 text-slate-700">{{ $task['description'] ?: '—' }}</div>
            </x-ui.card>

            <x-ui.card :title="__('tasks::messages.attachments')">
                <div class="space-y-2" wire:loading.class="opacity-60" wire:target="deleteAttachment">
                    @forelse($task['attachments'] as $attachment)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3" wire:key="task-attachment-{{ $attachment['id'] }}">
                            <div>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                                    <i class="fa-light fa-paperclip text-slate-400" aria-hidden="true"></i>
                                    <span>{{ $attachment['name'] }}</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-500">{{ number_format($attachment['size'] / 1024, 1) }} KB</div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button size="sm" variant="secondary" icon="fa-download" :href="route('tasks.attachments.download', [$task['id'], $attachment['id']])">{{ __('tasks::messages.download') }}</x-ui.button>
                                @can('tasks.update')
                                    <x-ui.button size="sm" variant="danger" icon="fa-trash" wire:click="deleteAttachment({{ $attachment['id'] }})" wire:confirm="{{ __('app.confirm_delete') }}" wire:loading.attr="disabled" wire:target="deleteAttachment({{ $attachment['id'] }})">{{ __('app.delete') }}</x-ui.button>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state :description="__('app.no_records')" />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card :title="__('tasks::messages.comments')">
                @can('tasks.comment')
                    <form class="mb-5 space-y-3" wire:submit="addComment">
                        <x-ui.textarea name="commentBody" :value="$commentBody" wire:model="commentBody" :placeholder="__('tasks::messages.comment_placeholder')" required />
                        <x-ui.button type="submit" icon="fa-comment-plus" wire:loading.attr="disabled" wire:target="addComment">
                            <span wire:loading.remove wire:target="addComment">{{ __('tasks::messages.new_comment') }}</span>
                            <span wire:loading wire:target="addComment">{{ __('app.loading') }}</span>
                        </x-ui.button>
                    </form>
                @endcan

                <div class="space-y-3">
                    @forelse($task['comments'] as $comment)
                        <article class="rounded-xl border border-slate-200 p-4" wire:key="task-comment-{{ $comment['id'] }}">
                            <div class="flex justify-between gap-3 text-xs text-slate-500"><span class="font-semibold text-slate-700">{{ $comment['user_name'] }}</span><span dir="ltr">{{ $comment['created_at']?->format('Y-m-d H:i') }}</span></div>
                            <div class="mt-3 whitespace-pre-line text-sm leading-7">{{ $comment['body'] }}</div>
                        </article>
                    @empty
                        <x-ui.empty-state :description="__('app.no_records')" />
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <aside class="space-y-4">
            @can('tasks.update')
                <form wire:submit="updateStatus">
                    <x-ui.card :title="__('tasks::messages.change_status')">
                        <div class="space-y-4">
                            <x-ui.select name="status" :label="__('tasks::messages.change_status')" wire:model="status">
                                @foreach($statuses as $statusItem)
                                    <option value="{{ $statusItem->value }}">{{ __('tasks::messages.status.'.$statusItem->value) }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.button class="w-full" type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="updateStatus">
                                <span wire:loading.remove wire:target="updateStatus">{{ __('app.save') }}</span>
                                <span wire:loading wire:target="updateStatus">{{ __('app.loading') }}</span>
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            @endcan

            <x-ui.card>
                <div class="space-y-4">
                    <x-ui.meta-item :label="__('tasks::messages.assignee')">{{ $task['assignee_name'] ?: __('tasks::messages.unassigned') }}</x-ui.meta-item>
                    <x-ui.meta-item :label="__('tasks::messages.due_at')"><span dir="ltr">{{ $task['due_at'] ? str_replace('T', ' ', $task['due_at']) : '—' }}</span></x-ui.meta-item>
                    <x-ui.meta-item :label="__('tasks::messages.creator')">{{ $task['creator_name'] }}</x-ui.meta-item>
                    <x-ui.meta-item :label="__('tasks::messages.estimated_minutes')">{{ $task['estimated_minutes'] ?? '—' }}</x-ui.meta-item>
                    <x-ui.meta-item :label="__('tasks::messages.spent_minutes')">{{ $task['spent_minutes'] ?? '—' }}</x-ui.meta-item>
                </div>
            </x-ui.card>
        </aside>
    </div>
</div>
