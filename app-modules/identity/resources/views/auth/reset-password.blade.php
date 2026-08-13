<div>
    <x-ui.card>
        <div class="mb-6">
            <h1 class="text-2xl font-black tracking-tight text-slate-950">تنظیم رمز عبور</h1>
            <p class="mt-1 text-sm text-slate-500">یک رمز عبور جدید برای حساب خود تعیین کنید.</p>
        </div>

        <form wire:submit="resetPassword" class="space-y-4">
            <x-ui.input name="email" label="ایمیل" type="email" dir="ltr" :value="$email" wire:model="email" required autocomplete="email" />
            <x-ui.input name="password" label="رمز عبور جدید" type="password" wire:model="password" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" label="تکرار رمز عبور" type="password" wire:model="password_confirmation" required autocomplete="new-password" />
            <x-ui.button class="w-full" type="submit" wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">ذخیره رمز عبور</span>
                <span wire:loading wire:target="resetPassword">{{ __('app.loading') }}</span>
            </x-ui.button>
        </form>
    </x-ui.card>
</div>
