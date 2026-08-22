<?php

namespace Modules\Projects\Application;

use App\Integration\Outbox\OutboxRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Application\Events\ProjectTaskStatusChangedV1;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;

class ProjectWorkflowManager
{
    public function __construct(
        private readonly AccountDirectory $accounts,
        private readonly ClientStatusQuery $clients,
        private readonly OutboxRecorder $outbox,
    ) {}

    public function create(int $actorId, Project $project, string $title): ProjectTaskStatus
    {
        $this->assertAdmin($actorId);
        $title = $this->title($title);

        return DB::transaction(function () use ($actorId, $project, $title): ProjectTaskStatus {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectMutable($project);
            $position = ((int) $project->taskStatuses()->active()->max('position')) + 10;
            $status = $project->taskStatuses()->create([
                'title' => $title,
                'position' => $position,
                'is_done' => false,
                'is_active' => true,
                'created_by' => $actorId,
            ]);
            $this->assertWorkflowValid($project);

            return $status;
        });
    }

    public function rename(int $actorId, ProjectTaskStatus $status, string $title): ProjectTaskStatus
    {
        $this->assertAdmin($actorId);
        $title = $this->title($title);

        return DB::transaction(function () use ($status, $title): ProjectTaskStatus {
            $status = ProjectTaskStatus::query()->lockForUpdate()->findOrFail($status->id);
            $project = Project::query()->lockForUpdate()->findOrFail($status->project_id);
            $this->assertProjectMutable($project);

            if ($status->title !== $title) {
                $status->update(['title' => $title]);
            }

            return $status->refresh();
        });
    }

    /** @param array<int, int> $orderedStatusIds */
    public function reorder(int $actorId, Project $project, array $orderedStatusIds): void
    {
        $this->assertAdmin($actorId);

        DB::transaction(function () use ($project, $orderedStatusIds): void {
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
        });
    }

    public function setDone(int $actorId, ProjectTaskStatus $status): void
    {
        $this->assertAdmin($actorId);

        DB::transaction(function () use ($actorId, $status): void {
            $status = ProjectTaskStatus::query()->lockForUpdate()->findOrFail($status->id);
            $project = Project::query()->lockForUpdate()->findOrFail($status->project_id);
            $this->assertProjectMutable($project);

            if (! $status->is_active) {
                throw new DomainException('Inactive Project Status cannot become Done.');
            }

            $previous = $project->taskStatuses()->active()->lockForUpdate()->firstWhere('is_done', true);
            if ($previous?->id === $status->id) {
                return;
            }

            ProjectTaskStatus::query()
                ->where('project_id', $project->id)
                ->where('is_active', true)
                ->where('is_done', true)
                ->update(['is_done' => false]);
            $status->update(['is_done' => true]);
            $this->assertWorkflowValid($project);

            $this->outbox->record(new ProjectTaskStatusChangedV1(
                eventId: (string) Str::uuid(),
                correlationId: (string) Str::uuid(),
                occurredAt: now()->toIso8601String(),
                projectId: $project->id,
                projectTaskStatusId: $status->id,
                isDone: true,
                actorId: $actorId,
            ));

        });
    }

    public function inactivate(int $actorId, ProjectTaskStatus $status): void
    {
        $this->assertAdmin($actorId);

        DB::transaction(function () use ($status): void {
            $status = ProjectTaskStatus::query()->lockForUpdate()->findOrFail($status->id);
            $project = Project::query()->lockForUpdate()->findOrFail($status->project_id);
            $this->assertProjectMutable($project);

            if (! $status->is_active) {
                return;
            }
            if ($status->is_done) {
                throw new DomainException('Set another active Project Status as Done before inactivating the current Done status.');
            }
            if ($project->taskStatuses()->active()->count() <= 2) {
                throw new DomainException('A Project must retain at least two active statuses.');
            }

            $status->update(['is_active' => false, 'inactivated_at' => now()]);
            $this->assertWorkflowValid($project);
        });
    }

    private function assertWorkflowValid(Project $project): void
    {
        $active = $project->taskStatuses()->active()->get(['id', 'is_done']);

        if ($active->count() < 2 || $active->where('is_done', true)->count() !== 1 || $active->where('is_done', false)->isEmpty()) {
            throw new DomainException('A Project must retain an active Open status and exactly one active Done status.');
        }
    }

    private function assertAdmin(int $actorId): void
    {
        $actor = $this->accounts->find($actorId);

        if ($actor === null || ! $actor->isActive || $actor->role !== UserRole::Admin) {
            throw new DomainException('Only an active Admin may manage Project workflow.');
        }
    }

    private function assertProjectMutable(Project $project): void
    {
        if (! $project->isActive() || $this->clients->find($project->client_id)?->isActive !== true) {
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
