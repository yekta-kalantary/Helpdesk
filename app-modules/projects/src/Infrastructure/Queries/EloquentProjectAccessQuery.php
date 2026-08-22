<?php

namespace Modules\Projects\Infrastructure\Queries;

use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Application\Contracts\ProjectAccessQuery;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;

final readonly class EloquentProjectAccessQuery implements ProjectAccessQuery
{
    public function __construct(
        private AccountDirectory $accounts,
        private ClientStatusQuery $clients,
        private ProjectMembershipDirectory $memberships,
    ) {}

    public function canAccessProject(int $projectId, int $accountId): bool
    {
        $account = $this->accounts->find($accountId);
        $project = $this->memberships->findProject($projectId);

        if ($account === null || $project === null || ! $account->isActive) {
            return false;
        }

        if ($account->role === UserRole::Admin) {
            return true;
        }

        if (! $project->isActive || ! $this->memberships->hasActiveMembership($projectId, $accountId)) {
            return false;
        }

        if ($account->role === UserRole::Customer) {
            return $account->clientId === $project->clientId
                && $this->clients->find($project->clientId)?->isActive === true;
        }

        return $account->role === UserRole::Employee;
    }
}
