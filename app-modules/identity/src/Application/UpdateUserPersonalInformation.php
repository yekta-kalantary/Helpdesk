<?php

namespace Modules\Identity\Application;

use Modules\Identity\Infrastructure\Models\User;

class UpdateUserPersonalInformation
{
    /**
     * @param  array{name: string, last_name: string}  $data
     */
    public function execute(User $user, array $data): void
    {
        $user->update([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
        ]);
    }
}
