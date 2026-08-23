<?php

namespace Modules\Projects\Infrastructure\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Projects\Application\DTOs\ProjectSummary;
use Modules\Projects\Application\DTOs\ProjectTaskStatusSummary;
use Modules\Projects\Application\DTOs\WorkGroupSummary;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Projects\Infrastructure\Models\WorkGroup;

final class EloquentProjectMembershipDirectory implements ProjectMembershipDirectory
{
    public function findProject(int $projectId): ?ProjectSummary
    {
        $project = Project::query()->find($projectId);

        return $project === null
            ? null
            : new ProjectSummary($project->id, $project->client_id, $project->status === ProjectStatus::Active);
    }

    public function hasActiveMembership(int $projectId, int $accountId): bool
    {
        return DB::table('project_user')
            ->where('project_id', $projectId)
            ->where('user_id', $accountId)
            ->whereNull('removed_at')
            ->exists();
    }

    public function defaultOpenTaskStatus(int $projectId): ?ProjectTaskStatusSummary
    {
        $status = ProjectTaskStatus::query()
            ->active()
            ->where('project_id', $projectId)
            ->where('is_done', false)
            ->orderBy('position')
            ->orderBy('id')
            ->first();

        return $status === null ? null : $this->taskStatusSummary($status);
    }

    public function findActiveTaskStatus(int $statusId): ?ProjectTaskStatusSummary
    {
        $status = ProjectTaskStatus::query()->active()->find($statusId);

        return $status === null ? null : $this->taskStatusSummary($status);
    }

    public function findActiveWorkGroup(int $workGroupId): ?WorkGroupSummary
    {
        $workGroup = WorkGroup::query()->active()->find($workGroupId);

        return $workGroup === null ? null : new WorkGroupSummary($workGroup->id, $workGroup->project_id);
    }

    public function findProjectForUpdate(int $projectId): int
    {
        return (int) Project::query()->whereKey($projectId)->lockForUpdate()->firstOrFail()->id;
    }

    private function taskStatusSummary(ProjectTaskStatus $status): ProjectTaskStatusSummary
    {
        return new ProjectTaskStatusSummary($status->id, $status->project_id, $status->is_done);
    }
}
