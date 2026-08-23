<?php

namespace Modules\Tasks\Presentation\Policies;

use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Tasks\Application\TaskAccess;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskPolicy
{
    public function __construct(
        private readonly AccountAuthenticationEligibility $eligibility,
        private readonly AccountDirectory $accounts,
        private readonly TaskAccess $access,
    ) {}

    public function viewAny(object $account): bool
    {
        return $this->eligibility->canAuthenticateAccount((int) $account->id);
    }

    public function view(object $account, Task $task): bool
    {
        return $this->access->canAccess((int) $account->id, $task);
    }

    public function create(object $account): bool
    {
        return $this->eligibility->canAuthenticateAccount((int) $account->id);
    }

    public function update(object $account, Task $task): bool
    {
        return $this->isActiveAdmin($account);
    }

    public function delete(object $account, Task $task): bool
    {
        return false;
    }

    private function isActiveAdmin(object $account): bool
    {
        $summary = $this->accounts->find((int) $account->id);

        return $summary !== null && $summary->isActive && $summary->role === UserRole::Admin;
    }
}
