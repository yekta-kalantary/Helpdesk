<div>
    <x-ui.page-header :title="__('identity::messages.new_user')" />

    <form class="max-w-2xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div>
                    <h2 class="text-base font-bold text-slate-950">{{ __('identity::messages.general_info') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.create_user_hint') }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                    <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-user-plus" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('identity::messages.create_user') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
