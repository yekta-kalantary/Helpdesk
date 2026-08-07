<div>
    <x-ui.page-header :title="$customerId ? __('customers::messages.edit_customer') : __('customers::messages.new_customer')" />

    <form class="max-w-4xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                    <x-ui.input name="company" :label="__('customers::messages.company')" :value="$company" wire:model="company" />
                    <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$email" wire:model="email" required />
                    <x-ui.input name="phone" :label="__('customers::messages.phone')" dir="ltr" :value="$phone" wire:model="phone" />
                    <x-ui.select name="status" :label="__('app.status')" wire:model="status">
                        @foreach($statuses as $statusItem)
                            <option value="{{ $statusItem->value }}">{{ __('customers::messages.status.'.$statusItem->value) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.textarea name="notes" :label="__('customers::messages.notes')" :value="$notes" wire:model="notes" />

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <x-ui.checkbox name="portal_enabled" :label="__('customers::messages.portal_enabled')" model="portal_enabled" />
                    <div class="mt-4 grid gap-4 sm:grid-cols-2" @if(! $portal_enabled) aria-disabled="true" @endif>
                        <x-ui.input name="portal_last_name" :label="__('app.last_name')" :value="$portal_last_name" wire:model="portal_last_name" :disabled="! $portal_enabled" :required="$portal_enabled" />
                        <x-ui.input name="portal_mobile" :label="__('app.mobile')" dir="ltr" :value="$portal_mobile" wire:model="portal_mobile" :disabled="! $portal_enabled" :required="$portal_enabled" />
                        <x-ui.input name="portal_password" :label="__('customers::messages.portal_password')" type="password" wire:model="portal_password" :disabled="! $portal_enabled" :hint="$customerId ? __('customers::messages.portal_password_hint') : null" />
                        <x-ui.input name="portal_password_confirmation" :label="__('customers::messages.portal_password_confirmation')" type="password" wire:model="portal_password_confirmation" :disabled="! $portal_enabled" />
                    </div>
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('customers.index')" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
