<div>
    <section aria-labelledby="forgot-password-heading">
        <div class="mb-5"><h1 id="forgot-password-heading" class="text-xl font-black text-workspace-text">بازیابی رمز عبور</h1><p class="mt-1 text-sm leading-6 text-workspace-muted">ایمیل حساب خود را وارد کنید.</p></div>

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

        <a href="{{ route('login') }}" wire:navigate class="mt-5 block text-center text-sm font-semibold text-slate-600 hover:text-slate-950">بازگشت به ورود</a>
    </section>
</div>
