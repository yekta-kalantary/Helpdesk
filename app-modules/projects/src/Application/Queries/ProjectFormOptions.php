<?php

namespace Modules\Projects\Application\Queries;

use Modules\Identity\Infrastructure\Models\User;

class ProjectFormOptions
{
    /** @return array<int,array{id:int,name:string}> */
    public function members(): array
    {
        return User::query()
            ->where('is_admin', false)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->full_name,
            ])
            ->all();
    }
}
