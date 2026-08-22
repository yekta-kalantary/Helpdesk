<?php

namespace App\Policies;

use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskPolicy
{
    public function __construct(private AccountAuthenticationEligibility $eligibility) {}

    public function viewAny(User $user): bool
    {
        return $this->eligibility->canAuthenticateAccount($user->id);
    }

    public function view(User $user, Task $task): bool
    {
        return Task::query()->visibleTo($user)->whereKey($task)->exists();
    }

    public function create(User $user): bool
    {
        return $this->eligibility->canAuthenticateAccount($user->id);
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
