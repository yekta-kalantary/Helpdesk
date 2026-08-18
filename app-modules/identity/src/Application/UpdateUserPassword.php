<?php

namespace Modules\Identity\Application;

use Illuminate\Support\Str;
use Modules\Identity\Infrastructure\Models\User;

class UpdateUserPassword
{
    public function execute(User $user, string $password): void
    {
        $user->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();
    }
}
