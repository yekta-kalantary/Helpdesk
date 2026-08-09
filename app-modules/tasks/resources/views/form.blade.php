<div>
    <x-ui.page-header :title="$taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task')" />

    <form class="max-w-5xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" :label="__('app.title')" :value="$title" wire:model="title" required />
                    <x-ui.select name="project_id" :label="__('tasks::messages.project')" wire:model.number="project_id" required>
                        <option value="">—</option>
                        @foreach($options['projects'] as $project)
                            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="assigned_to" :label="__('tasks::messages.assignee')" wire:model.number="assigned_to">
                        <option value="">{{ __('tasks::messages.unassigned') }}</option>
                        @foreach($options['members'] as $member)
                            <option value="{{ $member['id'] }}">{{ $member['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="priority" :label="__('tasks::messages.priority')" wire:model="priority">
                        @foreach($priorities as $priorityItem)
                            <option value="{{ $priorityItem->value }}">{{ __('tasks::messages.priority.'.$priorityItem->value) }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="status" :label="__('app.status')" wire:model="status">
                        @foreach($statuses as $statusItem)
                            <option value="{{ $statusItem->value }}">{{ __('tasks::messages.status.'.$statusItem->value) }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="due_at" :label="__('tasks::messages.due_at')" type="datetime-local" dir="ltr" :value="$due_at" wire:model="due_at" />
                    <x-ui.input name="estimated_minutes" :label="__('tasks::messages.estimated_minutes')" type="number" min="0" :value="$estimated_minutes" wire:model.number="estimated_minutes" />
                    <x-ui.input name="spent_minutes" :label="__('tasks::messages.spent_minutes')" type="number" min="0" :value="$spent_minutes" wire:model.number="spent_minutes" />
                </div>

                <x-ui.checkbox name="is_customer_visible" :label="__('tasks::messages.customer_visible')" model="is_customer_visible" />
                <x-ui.textarea name="description" :label="__('app.description')" :value="$description" wire:model="description" />
                <x-ui.input name="attachments" :label="__('tasks::messages.add_attachments')" type="file" wire:model="attachments" multiple />
                @error('attachments.*')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror

                @if($attachments)
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <i class="fa-light fa-paperclip" aria-hidden="true"></i>
                        <span>{{ count($attachments) }} {{ __('tasks::messages.attachments') }}</span>
                    </div>
                @endif

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save,attachments">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('tasks.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
