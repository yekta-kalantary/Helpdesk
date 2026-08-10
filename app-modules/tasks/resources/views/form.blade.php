<div>
    <x-ui.page-header :title="$taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task')" />

    <form class="max-w-3xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" :label="__('app.title')" :value="$title" wire:model="title" required />
                    <x-ui.select name="project_id" :label="__('tasks::messages.project')" wire:model.number="project_id" required>
                        <option value="">—</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->title }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.textarea name="description" :label="__('app.description')" :value="$description" wire:model="description" />
                <x-ui.checkbox name="is_done" label="انجام شده" model="is_done" />

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('tasks.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
