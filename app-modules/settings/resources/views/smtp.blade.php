<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('settings::messages.smtp')" :subtitle="__('settings::messages.local_fallback')" />

    <form class="max-w-3xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <x-ui.checkbox name="enabled" :label="__('settings::messages.enabled')" model="enabled" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="host" :label="__('settings::messages.host')" dir="ltr" :value="$host" wire:model="host" />
                    <x-ui.input name="port" :label="__('settings::messages.port')" type="number" min="1" max="65535" dir="ltr" :value="$port" wire:model.number="port" required />
                    <x-ui.input name="username" :label="__('settings::messages.username')" dir="ltr" :value="$username" wire:model="username" />
                    <x-ui.input name="password" :label="__('settings::messages.password')" type="password" autocomplete="new-password" wire:model="password" :hint="$passwordConfigured ? __('settings::messages.password_configured') : null" />
                    <x-ui.select name="scheme" :label="__('settings::messages.scheme')" wire:model="scheme">
                        <option value="">{{ __('settings::messages.scheme.auto') }}</option>
                        <option value="smtp">{{ __('settings::messages.scheme.smtp') }}</option>
                        <option value="smtps">{{ __('settings::messages.scheme.smtps') }}</option>
                    </x-ui.select>
                    <x-ui.input name="from_address" :label="__('settings::messages.from_address')" type="email" dir="ltr" :value="$from_address" wire:model="from_address" required />
                    <div class="sm:col-span-2">
                        <x-ui.input name="from_name" :label="__('settings::messages.from_name')" :value="$from_name" wire:model="from_name" required />
                    </div>
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
