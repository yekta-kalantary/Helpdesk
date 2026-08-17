<div>
    <section aria-labelledby="forgot-password-heading">
        <div class="mb-5"><h1 id="forgot-password-heading" class="text-heading-lg font-black text-text">بازیابی رمز عبور</h1><p class="mt-1 text-body-sm leading-6 text-text-muted">ایمیل حساب خود را وارد کنید.</p></div>

        @if($sent)
            <x-ui.alert class="mb-4" tone="success">
                اگر حسابی با این ایمیل وجود داشته باشد، لینک بازیابی برای آن ارسال می‌شود.
            </x-ui.alert>
        @endif

        <form wire:submit="send" class="space-y-5">
            <x-ui.input name="email" label="ایمیل" type="email" dir="ltr" :value="$email" wire:model="email" required autofocus autocomplete="email" />
            <x-ui.button class="w-full" type="submit" wire:loading.attr="disabled" wire:target="send">
                <span wire:loading.remove wire:target="send">ارسال لینک بازیابی</span>
                <span wire:loading wire:target="send">{{ __('app.loading') }}</span>
            </x-ui.button>
        </form>

        <a href="{{ route('login') }}" wire:navigate class="mt-5 inline-flex min-h-11 w-full items-center justify-center px-3 text-center text-body-sm font-semibold text-text-muted hover:text-text">بازگشت به ورود</a>
    </section>
</div>
