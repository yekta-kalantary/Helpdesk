<?php

namespace Modules\Identity\Presentation\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;

#[Layout('layouts::guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower(trim($credentials['email']));
        $key = 'login:'.sha1($email.'|'.request()->ip());

        if (! RateLimiter::attempt($key, 5, static fn (): bool => true, 60)) {
            $this->addError('email', __('identity::messages.too_many_login_attempts'));

            return null;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || blank($user->password) || ! Hash::check($credentials['password'], $user->password)) {
            $this->addError('email', __('identity::messages.invalid_credentials'));
            $this->reset('password');

            return null;
        }

        if (! $user->canAuthenticate()) {
            $this->addError('email', __('identity::messages.inactive_account'));
            $this->reset('password');

            return null;
        }

        RateLimiter::clear($key);
        Auth::login($user, $this->remember);
        session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('identity::auth.login')->title(__('identity::messages.login_title'));
    }
}
