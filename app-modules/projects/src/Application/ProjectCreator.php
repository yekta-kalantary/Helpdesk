<?php

namespace Modules\Projects\Application;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Infrastructure\Models\Project;

class ProjectCreator
{
    public function __construct(private readonly AccountDirectory $accounts) {}

    /** @param array<string, mixed> $attributes */
    public function create(int $creatorId, array $attributes): Project
    {
        $creator = $this->accounts->find($creatorId);

        if ($creator === null || ! $creator->isActive || $creator->role !== UserRole::Admin) {
            throw new DomainException('Only an active Admin may create Projects.');
        }

        return DB::transaction(function () use ($creatorId, $attributes): Project {
            $project = Project::query()->create($attributes);

            foreach ([
                ['title' => 'باز', 'position' => 10, 'is_done' => false],
                ['title' => 'در حال انجام', 'position' => 20, 'is_done' => false],
                ['title' => 'انجام‌شده', 'position' => 30, 'is_done' => true],
            ] as $status) {
                $project->taskStatuses()->create($status + [
                    'created_by' => $creatorId,
                    'is_active' => true,
                ]);
            }

            return $project;
        });
    }
}
