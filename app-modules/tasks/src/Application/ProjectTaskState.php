<?php

namespace Modules\Tasks\Application;

use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Tasks\Application\Contracts\ProjectTaskStateQuery;
use Modules\Tasks\Application\Contracts\ProjectTaskStateWriter;
use Modules\Tasks\Application\DTOs\TaskProjectContext;
use Modules\Tasks\Application\DTOs\TaskStatusContext;
use Modules\Tasks\Application\DTOs\TaskWorkGroupContext;
use Modules\Tasks\Infrastructure\Models\Task;

final class ProjectTaskState implements ProjectTaskStateQuery, ProjectTaskStateWriter
{
    public function __construct(private readonly ProjectMembershipDirectory $projects) {}

    public function findProject(int $projectId): ?TaskProjectContext
    {
        $project = $this->projects->findProject($projectId);

        return $project === null ? null : new TaskProjectContext($project->id, $project->clientId, $project->isActive);
    }

    public function defaultOpenStatus(int $projectId): ?TaskStatusContext
    {
        $status = $this->projects->defaultOpenTaskStatus($projectId);

        return $status === null ? null : new TaskStatusContext($status->id, $status->projectId, $status->isDone);
    }

    public function findActiveStatus(int $statusId): ?TaskStatusContext
    {
        $status = $this->projects->findActiveTaskStatus($statusId);

        return $status === null ? null : new TaskStatusContext($status->id, $status->projectId, $status->isDone);
    }

    public function findActiveWorkGroup(int $workGroupId): ?TaskWorkGroupContext
    {
        $workGroup = $this->projects->findActiveWorkGroup($workGroupId);

        return $workGroup === null ? null : new TaskWorkGroupContext($workGroup->id, $workGroup->projectId);
    }

    public function clearAssignmentsForRemovedMember(int $projectId, int $accountId): void
    {
        Task::query()->where('project_id', $projectId)->where('assigned_to', $accountId)->update(['assigned_to' => null]);
    }

    public function synchronizeStatusCompletion(int $projectId, int $statusId, bool $isDone, ?int $previousDoneStatusId): void
    {
        Task::query()->where('project_id', $projectId)->where('project_status_id', $statusId)->update(['completed_at' => $isDone ? now() : null]);

        if ($previousDoneStatusId !== null) {
            Task::query()->where('project_id', $projectId)->where('project_status_id', $previousDoneStatusId)->update(['completed_at' => null]);
        }
    }
}
