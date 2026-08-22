<?php

namespace Modules\Projects\Application;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;

class ProjectLifecycle
{
    public function __construct(private readonly AccountDirectory $accounts) {}

    public function complete(Project $project, int $actorId): Project
    {
        $this->assertAdmin($actorId);

        return DB::transaction(function () use ($project): Project {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);

            if ($project->status === ProjectStatus::Completed) {
                return $project;
            }

            $project->update(['status' => ProjectStatus::Completed]);

            return $project->refresh();
        });
    }

    public function reopen(Project $project, int $actorId): Project
    {
        $this->assertAdmin($actorId);

        if ($project->status === ProjectStatus::Active) {
            return $project;
        }

        return DB::transaction(function () use ($project): Project {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $project->update(['status' => ProjectStatus::Active]);

            return $project->refresh();
        });
    }

    private function assertAdmin(int $actorId): void
    {
        $actor = $this->accounts->find($actorId);

        if ($actor === null || ! $actor->isActive || $actor->role !== UserRole::Admin) {
            throw new DomainException('Only an active Admin may change Project lifecycle status.');
        }
    }
}
