<div>
    <x-ui.card>
        <div class="mb-6">
            <h1 class="text-2xl font-black tracking-tight text-slate-950">{{ __('identity::messages.login_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('app.name') }}</p>
        </div>

        @if(session('success'))
            <x-ui.alert class="mb-4" tone="success">{{ session('success') }}</x-ui.alert>
        @endif

        @if($errors->any())
            <x-ui.alert class="mb-4" tone="danger">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form wire:submit="login" class="space-y-4">
            <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$email" wire:model="email" required autofocus autocomplete="email" />
            <x-ui.input name="password" :label="__('app.password')" type="password" wire:model="password" required autocomplete="current-password" />
            <div class="flex items-center justify-between gap-4">
                <x-ui.checkbox name="remember" :label="__('app.remember_me')" model="remember" />
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-semibold text-slate-600 hover:text-slate-950">رمز عبور را فراموش کرده‌اید؟</a>
            </div>
            <x-ui.button class="w-full" type="submit" wire:loading.attr="disabled" wire:target="login">
                <span wire:loading.remove wire:target="login">{{ __('app.login') }}</span>
                <span wire:loading wire:target="login">{{ __('app.loading') }}</span>
            </x-ui.button>
        </form>
    </x-ui.card>
</div>
