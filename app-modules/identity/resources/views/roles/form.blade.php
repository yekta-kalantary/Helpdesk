<div>
    <x-ui.page-header :title="$roleId ? __('identity::messages.edit_role') : __('identity::messages.new_role')" />

    <form class="max-w-5xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="max-w-md">
                    <x-ui.input name="name" :label="__('identity::messages.role')" dir="ltr" :value="$name" wire:model="name" required />
                </div>

                <div>
                    <div class="mb-4">
                        <div class="text-sm font-semibold text-slate-700">{{ __('identity::messages.permissions') }}</div>
                        <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.permission_selection_hint') }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach(collect($permissionCatalog)->groupBy('module') as $module => $modulePermissions)
                            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <h2 class="mb-3 font-bold text-slate-900">{{ __('identity::messages.permission_modules.'.$module) }}</h2>
                                <div class="space-y-2">
                                    @foreach($modulePermissions as $permission)
                                        <x-ui.checkbox name="permissions[]" :label="$permission['name']" :value="$permission['name']" model="permissions" dir="ltr" />
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                    @error('permissions.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('roles.index')" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
