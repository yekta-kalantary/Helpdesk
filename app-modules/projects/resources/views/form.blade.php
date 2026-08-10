<div>
    <x-ui.page-header :title="$projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project')" />

    <form class="max-w-3xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <x-ui.input name="title" :label="__('app.title')" :value="$title" wire:model="title" required />
                <x-ui.textarea name="description" :label="__('app.description')" :value="$description" wire:model="description" />

                <div>
                    <div class="mb-2 text-sm font-semibold text-slate-700">{{ __('projects::messages.members') }}</div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @forelse($members as $member)
                            <x-ui.checkbox name="member_ids[]" :label="$member['name']" :value="$member['id']" model="member_ids" />
                        @empty
                            <p class="text-sm text-slate-500">{{ __('app.no_results') }}</p>
                        @endforelse
                    </div>
                    @error('member_ids.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('projects.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
