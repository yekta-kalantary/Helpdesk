<?php

namespace Modules\Identity\Application;

use Modules\Identity\Infrastructure\Models\User;

class UpdateUserContactInformation
{
    public function updateEmail(User $user, string $email): void
    {
        $user->update(['email' => $email]);
    }

    public function updateMobile(User $user, ?string $mobile): void
    {
        $user->update(['mobile' => $mobile]);
    }

    /**
     * @param  array{email: string, mobile?: string|null}  $data
     */
    public function execute(User $user, array $data): void
    {
        $user->update([
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
        ]);
    }
}
