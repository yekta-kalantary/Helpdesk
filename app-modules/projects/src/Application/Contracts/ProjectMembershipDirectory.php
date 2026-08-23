<?php

namespace Modules\Projects\Application\Contracts;

use Modules\Projects\Application\DTOs\ProjectSummary;
use Modules\Projects\Application\DTOs\ProjectTaskStatusSummary;
use Modules\Projects\Application\DTOs\WorkGroupSummary;

interface ProjectMembershipDirectory
{
    public function findProject(int $projectId): ?ProjectSummary;

    public function hasActiveMembership(int $projectId, int $accountId): bool;

    public function defaultOpenTaskStatus(int $projectId): ?ProjectTaskStatusSummary;

    public function findActiveTaskStatus(int $statusId): ?ProjectTaskStatusSummary;

    public function findActiveWorkGroup(int $workGroupId): ?WorkGroupSummary;

    /**
     * Lock the project row for pending writes and return its identifier.
     *
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException when the project is missing
     */
    public function findProjectForUpdate(int $projectId): int;
}
