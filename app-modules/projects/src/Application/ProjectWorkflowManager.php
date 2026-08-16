<?php

namespace Modules\Projects\Application;

use App\Support\ActivityRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;

class ProjectWorkflowManager
{
    public function __construct(private readonly ActivityRecorder $activities) {}

    public function create(User $actor, Project $project, string $title): ProjectTaskStatus
    {
        $this->assertAdmin($actor);
        $this->assertProjectMutable($project);
        $title = $this->title($title);

        return DB::transaction(function () use ($actor, $project, $title): ProjectTaskStatus {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectMutable($project);
            $position = ((int) $project->taskStatuses()->active()->max('position')) + 10;

            $status = $project->taskStatuses()->create([
                'title' => $title,
                'position' => $position,
                'is_done' => false,
                'is_active' => true,
                'created_by' => $actor->id,
            ]);

            $this->activities->record($actor, 'project_status.created', $project, null, [
                'project_status_id' => $status->id,
                'title' => $status->title,
            ]);

            $this->assertWorkflowValid($project);

            return $status;
        });
    }

    public function rename(User $actor, ProjectTaskStatus $status, string $title): ProjectTaskStatus
    {
        $this->assertAdmin($actor);
        $title = $this->title($title);

        return DB::transaction(function () use ($actor, $status, $title): ProjectTaskStatus {
            $status = ProjectTaskStatus::query()->lockForUpdate()->findOrFail($status->id);
            $project = Project::query()->lockForUpdate()->findOrFail($status->project_id);
            $this->assertProjectMutable($project);
            $old = $status->title;

            if ($old === $title) {
                return $status;
            }

            $status->update(['title' => $title]);
            $this->activities->record($actor, 'project_status.renamed', $project, null, [
                'project_status_id' => $status->id,
                'old_title' => $old,
                'new_title' => $title,
            ]);

            return $status->refresh();
        });
    }

    /** @param array<int, int> $orderedStatusIds */
    public function reorder(User $actor, Project $project, array $orderedStatusIds): void
    {
        $this->assertAdmin($actor);

        DB::transaction(function () use ($actor, $project, $orderedStatusIds): void {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectMutable($project);
            $statuses = $project->taskStatuses()->active()->lockForUpdate()->get();
            $expected = $statuses->pluck('id')->sort()->values()->all();
            $actual = collect($orderedStatusIds)->map(fn ($id): int => (int) $id)->unique()->sort()->values()->all();

            if ($actual !== $expected || count($orderedStatusIds) !== count($expected)) {
                throw new DomainException('Status order must contain every active Project Status exactly once.');
            }

            foreach ($orderedStatusIds as $index => $statusId) {
                ProjectTaskStatus::query()->whereKey((int) $statusId)->update(['position' => ($index + 1) * 10]);
            }

            $this->activities->record($actor, 'project_status.reordered', $project, null, [
                'ordered_status_ids' => array_map('intval', $orderedStatusIds),
            ]);
        });
    }

    public function setDone(User $actor, ProjectTaskStatus $status): void
    {
        $this->assertAdmin($actor);

        DB::transaction(function () use ($actor, $status): void {
            $status = ProjectTaskStatus::query()->lockForUpdate()->findOrFail($status->id);
            $project = Project::query()->lockForUpdate()->findOrFail($status->project_id);
            $this->assertProjectMutable($project);

            if (! $status->is_active) {
                throw new DomainException('Inactive Project Status cannot become Done.');
            }

            $statuses = $project->taskStatuses()->active()->lockForUpdate()->get();
            $previous = $statuses->firstWhere('is_done', true);

            if ($previous?->id === $status->id) {
                return;
            }

            ProjectTaskStatus::query()
                ->where('project_id', $project->id)
                ->where('is_active', true)
                ->where('is_done', true)
                ->update(['is_done' => false]);
            $status->update(['is_done' => true]);

            $reopenedTaskCount = $previous
                ? $previous->tasks()->whereNotNull('completed_at')->update(['completed_at' => null])
                : 0;
            $completedTaskCount = $status->tasks()
                ->whereNull('completed_at')
                ->update(['completed_at' => now()]);

            $this->assertWorkflowValid($project);
            $this->activities->record($actor, 'project_status.done_changed', $project, null, [
                'previous_status_id' => $previous?->id,
                'previous_status_title_snapshot' => $previous?->title,
                'new_status_id' => $status->id,
                'new_status_title_snapshot' => $status->title,
                'reopened_task_count' => $reopenedTaskCount,
                'completed_task_count' => $completedTaskCount,
            ]);
        });
    }

    public function inactivate(User $actor, ProjectTaskStatus $status): void
    {
        $this->assertAdmin($actor);

        DB::transaction(function () use ($actor, $status): void {
            $status = ProjectTaskStatus::query()->lockForUpdate()->findOrFail($status->id);
            $project = Project::query()->lockForUpdate()->findOrFail($status->project_id);
            $this->assertProjectMutable($project);

            if (! $status->is_active) {
                return;
            }

            if ($status->tasks()->exists()) {
                throw new DomainException('A Project Status with Tasks cannot be inactivated until Tasks are moved.');
            }

            if ($status->is_done) {
                throw new DomainException('Set another active Project Status as Done before inactivating the current Done status.');
            }

            if ($project->taskStatuses()->active()->count() <= 2) {
                throw new DomainException('A Project must retain at least two active statuses.');
            }

            $status->update([
                'is_active' => false,
                'inactivated_at' => now(),
            ]);

            $this->assertWorkflowValid($project);
            $this->activities->record($actor, 'project_status.inactivated', $project, null, [
                'project_status_id' => $status->id,
                'title_snapshot' => $status->title,
            ]);
        });
    }

    private function assertWorkflowValid(Project $project): void
    {
        $active = $project->taskStatuses()->active()->get(['id', 'is_done']);

        if ($active->count() < 2) {
            throw new DomainException('A Project must have at least two active statuses.');
        }

        if ($active->where('is_done', true)->count() !== 1) {
            throw new DomainException('A Project must have exactly one active Done status.');
        }

        if ($active->where('is_done', false)->isEmpty()) {
            throw new DomainException('A Project must have at least one active Open status.');
        }
    }

    private function assertAdmin(User $actor): void
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw new DomainException('Only an active Admin may manage Project workflow.');
        }
    }

    private function assertProjectMutable(Project $project): void
    {
        $project->loadMissing('client');
        if (! $project->isActive() || ! $project->client->isActive()) {
            throw new DomainException('Completed or inactive Projects are read-only.');
        }
    }

    private function title(string $title): string
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) > 120) {
            throw new DomainException('Project Status title is required and may not exceed 120 characters.');
        }

        return $title;
    }
}
