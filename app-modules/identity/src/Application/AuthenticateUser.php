<?php

namespace Modules\Identity\Application;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Infrastructure\Models\User;

class AuthenticateUser
{
    public function execute(string $email, string $password, bool $remember, string $throttleKey): User
    {
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('identity::messages.login.rate_limited'),
            ]);
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user || ! Hash::check($password, (string) $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => __('identity::messages.invalid_credentials'),
            ]);
        }

        if (! $user->canAuthenticate()) {
            throw ValidationException::withMessages([
                'email' => __('identity::messages.inactive_account'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $remember);
        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }
}
