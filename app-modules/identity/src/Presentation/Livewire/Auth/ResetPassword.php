<?php

namespace Modules\Identity\Presentation\Livewire\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;

#[Layout('layouts::guest')]
class ResetPassword extends Component
{
    public string $token;

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = Str::lower(trim((string) request()->query('email')));
    }

    public function resetPassword(): mixed
    {
        $data = $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['email'] = Str::lower(trim($data['email']));
        $key = 'password-reset:'.sha1($data['email'].'|'.request()->ip());

        if (! RateLimiter::attempt($key, 5, static fn (): bool => true, 60)) {
            $this->addError('email', __('identity::messages.too_many_password_reset_attempts'));

            return null;
        }

        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return null;
        }

        RateLimiter::clear($key);
        session()->flash('success', 'رمز عبور با موفقیت تنظیم شد.');

        return redirect()->route('login');
    }

    public function render()
    {
        return view('identity::auth.reset-password')->title('تنظیم رمز عبور');
    }
}
