<?php

namespace Modules\Identity\Application;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Infrastructure\Models\User;

class CreateUser
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $passwordMode = $attributes['password_mode'];
            unset($attributes['password_mode']);

            if ($passwordMode === 'email') {
                $attributes['password'] = null;
                $attributes['is_active'] = false;
            }

            $user = User::query()->create($attributes);

            if ($passwordMode === 'email') {
                $status = Password::sendResetLink(['email' => $user->email]);

                if ($status !== Password::RESET_LINK_SENT) {
                    throw ValidationException::withMessages([
                        'email' => $status === Password::RESET_THROTTLED
                            ? __('identity::messages.password_recovery.rate_limited')
                            : __('identity::messages.password_recovery.generic_error'),
                    ]);
                }
            }

            return $user;
        });
    }
}
