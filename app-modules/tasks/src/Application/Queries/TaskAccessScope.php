<?php

namespace Modules\Tasks\Application\Queries;

use Modules\Identity\Infrastructure\Models\User;

class TaskAccessScope
{
    /** @return array{actor_id:int,manage_all:bool} */
    public function for(User $user): array
    {
        return [
            'actor_id' => $user->id,
            'manage_all' => $user->hasRole('admin') || $user->can('tasks.manage_all'),
        ];
    }
}
