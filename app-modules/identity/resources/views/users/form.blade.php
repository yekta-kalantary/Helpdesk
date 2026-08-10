<div>
    <x-ui.page-header :title="$userId ? __('identity::messages.edit_user') : __('identity::messages.new_user')" />

    <form class="max-w-3xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                    <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                    <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$email" wire:model="email" required />
                    <x-ui.input name="mobile" :label="__('app.mobile')" dir="ltr" :value="$mobile" wire:model="mobile" />
                    <x-ui.input name="password" :label="__('app.password')" type="password" wire:model="password" :required="! $userId" :hint="$userId ? __('identity::messages.leave_password_blank') : null" />
                    <x-ui.input name="password_confirmation" :label="__('identity::messages.password_confirmation')" type="password" wire:model="password_confirmation" :required="! $userId" />
                </div>

                <x-ui.checkbox name="is_active" :label="__('identity::messages.is_active')" model="is_active" />

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
