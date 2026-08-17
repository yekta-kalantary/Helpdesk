<div>
    <section aria-labelledby="reset-password-heading">
        <div class="mb-5"><h1 id="reset-password-heading" class="text-heading-lg font-black text-text">تنظیم رمز عبور</h1><p class="mt-1 text-body-sm leading-6 text-text-muted">یک رمز عبور جدید برای حساب خود تعیین کنید.</p></div>

        <form wire:submit="resetPassword" class="space-y-5">
            <x-ui.input name="email" label="ایمیل" type="email" dir="ltr" :value="$email" wire:model="email" required autocomplete="email" />
            <x-ui.input name="password" label="رمز عبور جدید" type="password" wire:model="password" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" label="تکرار رمز عبور" type="password" wire:model="password_confirmation" required autocomplete="new-password" />
            <x-ui.button class="w-full" type="submit" wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">ذخیره رمز عبور</span>
                <span wire:loading wire:target="resetPassword">{{ __('app.loading') }}</span>
            </x-ui.button>
        </form>
    </section>
</div>
