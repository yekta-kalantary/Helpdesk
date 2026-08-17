<div>
    <x-ui.page-header :title="__('identity::messages.new_user')" subtitle="یک کاربر مشتری بسازید و سطح دسترسی او را از طریق مشتری تعیین کنید." />

    <form class="max-w-3xl" wire:submit="save">
        <div class="space-y-4">
            <x-ui.card title="اطلاعات کاربر" subtitle="نام و راه‌های تماس کاربر را وارد کنید.">
                <div class="space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                        <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="email" type="email" label="ایمیل" :value="$email" wire:model="email" dir="ltr" required />
                        <x-ui.input name="mobile" label="موبایل" :value="$mobile" wire:model="mobile" dir="ltr" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="مشتری و وضعیت" subtitle="نقش این حساب به‌صورت خودکار Customer است.">
                <div class="space-y-5">
                    <x-ui.select name="client_id" label="مشتری" wire:model.number="client_id" required>
                        <option value="">—</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.checkbox name="is_active" label="کاربر فعال باشد" model="is_active" />
                    <p class="text-sm leading-6 text-slate-500">پس از ایجاد کاربر فعال، لینک تنظیم رمز عبور به ایمیل او ارسال می‌شود.</p>
                </div>
            </x-ui.card>

            <x-ui.form-actions class="sticky bottom-0 z-10 -mx-4 bg-workspace-page/95 px-4 pb-1 backdrop-blur sm:static sm:mx-0 sm:bg-transparent sm:px-0 sm:pb-0 sm:backdrop-blur-none">
                <x-ui.button type="submit" icon="fa-user-plus" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ __('identity::messages.create_user') }}</span>
                    <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                </x-ui.button>
                <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
            </x-ui.form-actions>
        </div>
    </form>
</div>
