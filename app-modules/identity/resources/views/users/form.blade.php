<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$userId ? __('identity::messages.edit_user') : __('identity::messages.new_user')" />

    <div class="max-w-3xl">
        <div class="mb-5 flex gap-2 overflow-x-auto border-b border-slate-200" role="tablist">
            <button
                type="button"
                wire:click="$set('activeTab', 'general')"
                @class([
                    'whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition',
                    'border-slate-900 text-slate-900' => $activeTab === 'general',
                    'border-transparent text-slate-500 hover:text-slate-700' => $activeTab !== 'general',
                ])
            >
                {{ __('identity::messages.general_info') }}
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'contact')"
                @disabled(! $userId)
                @class([
                    'whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition',
                    'border-slate-900 text-slate-900' => $activeTab === 'contact',
                    'border-transparent text-slate-500 hover:text-slate-700' => $activeTab !== 'contact' && $userId,
                    'cursor-not-allowed border-transparent text-slate-300' => ! $userId,
                ])
            >
                {{ __('identity::messages.contact_info') }}
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'account')"
                @disabled(! $userId)
                @class([
                    'whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition',
                    'border-slate-900 text-slate-900' => $activeTab === 'account',
                    'border-transparent text-slate-500 hover:text-slate-700' => $activeTab !== 'account' && $userId,
                    'cursor-not-allowed border-transparent text-slate-300' => ! $userId,
                ])
            >
                {{ __('identity::messages.account_settings') }}
            </button>
        </div>

        @if($activeTab === 'general')
            <form wire:submit="saveGeneral">
                <x-ui.card>
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                            <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                        </div>

                        <x-ui.form-actions>
                            <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveGeneral">
                                <span wire:loading.remove wire:target="saveGeneral">{{ __('app.save') }}</span>
                                <span wire:loading wire:target="saveGeneral">{{ __('app.loading') }}</span>
                            </x-ui.button>
                            <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                        </x-ui.form-actions>
                    </div>
                </x-ui.card>
            </form>
        @elseif($activeTab === 'contact' && $userId)
            <form wire:submit="saveContact">
                <x-ui.card>
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$email" wire:model="email" />
                            <x-ui.input name="mobile" :label="__('app.mobile')" dir="ltr" :value="$mobile" wire:model="mobile" />
                        </div>

                        <x-ui.form-actions>
                            <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveContact">
                                <span wire:loading.remove wire:target="saveContact">{{ __('app.save') }}</span>
                                <span wire:loading wire:target="saveContact">{{ __('app.loading') }}</span>
                            </x-ui.button>
                            <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                        </x-ui.form-actions>
                    </div>
                </x-ui.card>
            </form>
        @elseif($activeTab === 'account' && $userId)
            <form wire:submit="saveAccount">
                <x-ui.card>
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="password" :label="__('app.password')" type="password" wire:model="password" :hint="__('identity::messages.leave_password_blank')" />
                            <x-ui.input name="password_confirmation" :label="__('identity::messages.password_confirmation')" type="password" wire:model="password_confirmation" />
                        </div>

                        <div>
                            <x-ui.checkbox name="is_active" :label="__('identity::messages.is_active')" model="is_active" />
                            @error('is_active')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <x-ui.form-actions>
                            <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveAccount">
                                <span wire:loading.remove wire:target="saveAccount">{{ __('app.save') }}</span>
                                <span wire:loading wire:target="saveAccount">{{ __('app.loading') }}</span>
                            </x-ui.button>
                            <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                        </x-ui.form-actions>
                    </div>
                </x-ui.card>
            </form>
        @endif
    </div>
</div>
