<?php

namespace Modules\Projects\Presentation\Policies;

use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Application\Contracts\ProjectAccessQuery;
use Modules\Projects\Infrastructure\Models\Project;

class ProjectPolicy
{
    public function __construct(
        private readonly ProjectAccessQuery $projects,
        private readonly AccountAuthenticationEligibility $eligibility,
        private readonly AccountDirectory $accounts,
    ) {}

    public function viewAny(object $account): bool
    {
        return $this->eligibility->canAuthenticateAccount($this->accountId($account));
    }

    public function view(object $account, Project $project): bool
    {
        return $this->projects->canAccessProject($project->id, $this->accountId($account));
    }

    public function create(object $account): bool
    {
        return $this->isActiveAdmin($account);
    }

    public function update(object $account, Project $project): bool
    {
        return $this->isActiveAdmin($account);
    }

    public function delete(object $account, Project $project): bool
    {
        return false;
    }

    private function isActiveAdmin(object $account): bool
    {
        $summary = $this->accounts->find($this->accountId($account));

        return $summary !== null && $summary->isActive && $summary->role === UserRole::Admin;
    }

    private function accountId(object $account): int
    {
        return (int) $account->id;
    }
}
