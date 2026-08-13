<?php

namespace Modules\Identity\Presentation\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::guest')]
class ForgotPassword extends Component
{
    public string $email = '';

    public bool $sent = false;

    public function send(): void
    {
        $this->validate(['email' => ['required', 'email']]);
        $this->email = Str::lower(trim($this->email));
        $key = 'password-reset:'.sha1($this->email.'|'.request()->ip());

        if (RateLimiter::attempt($key, 3, static fn (): bool => true, 60)) {
            Password::sendResetLink(['email' => $this->email]);
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('identity::auth.forgot-password')->title('بازیابی رمز عبور');
    }
}
