<div>
    <section aria-labelledby="login-heading">
        <div class="mb-5"><h1 id="login-heading" class="text-heading-lg font-semibold text-text">{{ __('identity::messages.login_title') }}</h1><p class="mt-1 text-body-sm leading-6 text-text-muted">برای ادامه وارد حساب خود شوید.</p></div>

        @if(session('success'))
            <x-ui.alert class="mb-4" tone="success">{{ session('success') }}</x-ui.alert>
        @endif

        @if($errors->any())
            <x-ui.alert class="mb-4" tone="danger">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form wire:submit="login" class="space-y-5">
            <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$email" wire:model="email" required autofocus autocomplete="email" />
            <x-ui.input name="password" :label="__('app.password')" type="password" wire:model="password" required autocomplete="current-password" />
            <div class="flex items-center justify-between gap-4">
                <x-ui.checkbox name="remember" :label="__('app.remember_me')" model="remember" />
                <a href="{{ route('password.request') }}" wire:navigate class="inline-flex min-h-11 items-center px-2 text-body-sm font-semibold text-text-muted hover:text-text">رمز عبور را فراموش کرده‌اید؟</a>
            </div>
            <x-ui.button class="w-full" type="submit" wire:loading.attr="disabled" wire:target="login">
                <span wire:loading.remove wire:target="login">{{ __('app.login') }}</span>
                <span wire:loading wire:target="login">{{ __('app.loading') }}</span>
            </x-ui.button>
        </form>
    </section>
</div>
