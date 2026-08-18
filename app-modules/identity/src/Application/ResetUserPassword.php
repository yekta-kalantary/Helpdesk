<?php

namespace Modules\Identity\Application;

use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Infrastructure\Models\User;

class ResetUserPassword
{
    public function execute(string $token, string $email, string $password): void
    {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordResetEvent($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => $status === Password::INVALID_TOKEN
                    ? __('identity::messages.password_reset.invalid_token')
                    : __('identity::messages.password_reset.generic_error'),
            ]);
        }
    }
}
