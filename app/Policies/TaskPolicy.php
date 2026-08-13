<?php

namespace App\Policies;

use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAuthenticate();
    }

    public function view(User $user, Task $task): bool
    {
        return Task::query()->visibleTo($user)->whereKey($task)->exists();
    }

    public function create(User $user): bool
    {
        return $user->canAuthenticate();
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    public function delete(User $user, Task $task): bool
    {
        return false;
    }
}
