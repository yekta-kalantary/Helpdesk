<?php

namespace Modules\Identity\Application;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class RequestPasswordReset
{
    public function execute(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => __('identity::messages.password_recovery.rate_limited'),
            ]);
        }
    }
}
