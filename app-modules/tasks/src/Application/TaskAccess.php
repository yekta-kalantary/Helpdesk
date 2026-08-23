<?php

namespace Modules\Tasks\Application;

use DomainException;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Tasks\Infrastructure\Models\Task;

final class TaskAccess
{
    public function __construct(
        private readonly AccountDirectory $accounts,
        private readonly AccountAuthenticationEligibility $eligibility,
        private readonly ProjectMembershipDirectory $projects,
        private readonly ClientStatusQuery $clients,
    ) {}

    public function canAccess(int $accountId, Task $task): bool
    {
        return $this->canAccessProjectOfTask($accountId, (int) $task->project_id);
    }

    public function canAccessTaskId(int $accountId, int $taskId): bool
    {
        $projectId = Task::query()->whereKey($taskId)->value('project_id');

        return $projectId !== null && $this->canAccessProjectOfTask($accountId, (int) $projectId);
    }

    public function canAccessProjectOfTask(int $accountId, int $projectId): bool
    {
        $account = $this->accounts->find($accountId);
        $project = $this->projects->findProject($projectId);

        if ($account === null || $project === null || ! $this->eligibility->canAuthenticateAccount($accountId)) {
            return false;
        }

        if ($account->role === UserRole::Admin) {
            return true;
        }

        return $this->projects->hasActiveMembership($project->id, $accountId)
            && ($account->role !== UserRole::Customer || $account->clientId === $project->clientId);
    }

    public function assertMutable(int $accountId, Task $task): void
    {
        if (! $this->canAccess($accountId, $task)) {
            throw new DomainException('Task access is not allowed.');
        }

        $project = $this->projects->findProject($task->project_id);
        $status = $this->projects->findActiveTaskStatus((int) $task->project_status_id);
        if ($project === null || ! $project->isActive || $this->clients->find($project->clientId)?->isActive !== true || $status?->isDone) {
            throw new DomainException('Closed projects and tasks are read-only for collaboration.');
        }
    }

    public function assertAdmin(int $accountId): void
    {
        $account = $this->accounts->find($accountId);
        if ($account === null || ! $account->isActive || $account->role !== UserRole::Admin) {
            throw new DomainException('Only an active admin may perform this action.');
        }
    }
}
