<?php

namespace Modules\Projects\Application;

use App\Support\ActivityRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Domain\Enums\TaskStatus;

class ProjectLifecycle
{
    public function __construct(private readonly ActivityRecorder $activities) {}

    public function complete(Project $project, User $actor): Project
    {
        $this->assertAdmin($actor);

        return DB::transaction(function () use ($project, $actor): Project {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);

            if ($project->status === ProjectStatus::Completed) {
                return $project;
            }

            $hasOpenTasks = $project->tasks()
                ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
                ->exists();

            if ($hasOpenTasks) {
                throw new DomainException('A project with non-terminal tasks cannot be completed.');
            }

            $project->update(['status' => ProjectStatus::Completed]);
            $this->activities->record($actor, 'project.status_changed', $project, null, [
                'old' => ProjectStatus::Active->value,
                'new' => ProjectStatus::Completed->value,
            ]);

            return $project->refresh();
        });
    }

    public function reopen(Project $project, User $actor): Project
    {
        $this->assertAdmin($actor);

        if ($project->status === ProjectStatus::Active) {
            return $project;
        }

        return DB::transaction(function () use ($project, $actor): Project {
            $project->update(['status' => ProjectStatus::Active]);
            $this->activities->record($actor, 'project.status_changed', $project, null, [
                'old' => ProjectStatus::Completed->value,
                'new' => ProjectStatus::Active->value,
            ]);

            return $project->refresh();
        });
    }

    private function assertAdmin(User $actor): void
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw new DomainException('Only an active admin may change project status.');
        }
    }
}
