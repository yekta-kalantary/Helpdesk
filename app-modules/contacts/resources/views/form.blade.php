<div>
    <x-ui.page-header :title="$contactId ? trim($first_name.' '.$last_name) : __('contacts::messages.new_contact')" />

    @php
        $tabs = [
            'general' => __('contacts::messages.general_info'),
            'contact-info' => __('contacts::messages.contact_info'),
        ];
        if ($canViewAccounts) {
            $tabs['account-settings'] = __('contacts::messages.account_settings');
        }
        $canManageAccount = $userId
            ? auth()->user()?->can('users.update')
            : auth()->user()?->can('users.create');
    @endphp

    <div class="mb-5 flex flex-wrap gap-2 border-b border-slate-200 pb-3">
        @foreach($tabs as $tabKey => $tabLabel)
            <button
                type="button"
                wire:click="setTab('{{ $tabKey }}')"
                @class([
                    'rounded-lg px-4 py-2 text-sm font-semibold transition',
                    'bg-slate-950 text-white' => $tab === $tabKey,
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $tab !== $tabKey,
                ])
            >
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    <form class="max-w-5xl" wire:submit="save">
        <x-ui.card>
            @if($tab === 'general')
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="first_name" :label="__('app.name_label')" :value="$first_name" wire:model="first_name" required />
                    <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                    <x-ui.select name="gender" :label="__('contacts::messages.gender')" wire:model="gender">
                        <option value="">{{ __('contacts::messages.gender_unspecified') }}</option>
                        <option value="male">{{ __('contacts::messages.gender_male') }}</option>
                        <option value="female">{{ __('contacts::messages.gender_female') }}</option>
                        <option value="other">{{ __('contacts::messages.gender_other') }}</option>
                    </x-ui.select>
                </div>
            @elseif($tab === 'contact-info')
                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$email" wire:model="email" required />
                        <x-ui.input name="mobile" :label="__('app.mobile')" dir="ltr" :value="$mobile" wire:model="mobile" required />
                        <x-ui.input name="province" :label="__('contacts::messages.province')" :value="$province" wire:model="province" />
                        <x-ui.input name="city" :label="__('contacts::messages.city')" :value="$city" wire:model="city" />
                        <x-ui.input name="postal_code" :label="__('contacts::messages.postal_code')" dir="ltr" :value="$postal_code" wire:model="postal_code" />
                    </div>
                    <x-ui.textarea name="address" :label="__('contacts::messages.address')" :value="$address" wire:model="address" />
                </div>
            @else
                <div class="space-y-5">
                    @unless($canManageAccount)
                        <x-ui.alert tone="warning">{{ __('contacts::messages.account_read_only') }}</x-ui.alert>
                    @endunless

                    <x-ui.checkbox name="account_enabled" :label="__('contacts::messages.login_enabled')" model="account_enabled" :disabled="! $canManageAccount" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.select name="role" :label="__('contacts::messages.role')" wire:model="role" :disabled="! $account_enabled || ! $canManageAccount" :required="$account_enabled">
                            <option value="">{{ __('contacts::messages.select_role') }}</option>
                            @foreach($roles as $roleItem)
                                <option value="{{ $roleItem }}">{{ $roleItem }}</option>
                            @endforeach
                        </x-ui.select>

                        <div class="hidden sm:block"></div>

                        <x-ui.input
                            name="password"
                            :label="__('contacts::messages.password')"
                            type="password"
                            wire:model="password"
                            :disabled="! $account_enabled || ! $canManageAccount"
                            :required="$account_enabled && ! $userId"
                            :hint="$userId ? __('contacts::messages.password_hint') : null"
                        />
                        <x-ui.input
                            name="password_confirmation"
                            :label="__('contacts::messages.password_confirmation')"
                            type="password"
                            wire:model="password_confirmation"
                            :disabled="! $account_enabled || ! $canManageAccount"
                            :required="$account_enabled && ! $userId"
                        />
                    </div>
                </div>
            @endif

            <x-ui.form-actions class="mt-6">
                @if($tab !== 'account-settings' || $canManageAccount)
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                @endif
                <x-ui.button variant="secondary" :href="route('contacts.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
            </x-ui.form-actions>
        </x-ui.card>
    </form>
</div>
