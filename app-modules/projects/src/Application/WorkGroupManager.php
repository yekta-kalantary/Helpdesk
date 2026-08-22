<?php

namespace Modules\Projects\Application;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\WorkGroup;

class WorkGroupManager
{
    private const MAX_DEPTH = 5;

    public function __construct(
        private readonly AccountDirectory $accounts,
        private readonly ClientStatusQuery $clients,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(int $actorId, Project $project, array $data): WorkGroup
    {
        $this->assertAdmin($actorId);

        return DB::transaction(function () use ($actorId, $project, $data): WorkGroup {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectMutable($project);
            $parent = $this->resolveParent($project, $data['parent_id'] ?? null);

            if (($parent?->depth() ?? 0) + 1 > self::MAX_DEPTH) {
                throw new DomainException('Work Group depth may not exceed five levels.');
            }

            return WorkGroup::query()->create([
                'project_id' => $project->id,
                'parent_id' => $parent?->id,
                'title' => $this->title((string) ($data['title'] ?? '')),
                'description' => $this->description($data['description'] ?? null),
                'position' => isset($data['position'])
                    ? max(0, (int) $data['position'])
                    : ((int) WorkGroup::query()->where('project_id', $project->id)->where('parent_id', $parent?->id)->max('position')) + 10,
                'status' => 'active',
                'created_by' => $actorId,
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(int $actorId, WorkGroup $group, array $data): WorkGroup
    {
        $this->assertAdmin($actorId);
        $group = WorkGroup::query()->findOrFail($group->id);

        if (array_key_exists('parent_id', $data)) {
            $parent = $data['parent_id'] ? WorkGroup::query()->findOrFail((int) $data['parent_id']) : null;
            $group = $this->move($actorId, $group, $parent);
        }

        return DB::transaction(function () use ($group, $data): WorkGroup {
            $group = WorkGroup::query()->lockForUpdate()->findOrFail($group->id);
            $project = Project::query()->lockForUpdate()->findOrFail($group->project_id);
            $this->assertProjectMutable($project);
            $attributes = [];

            if (array_key_exists('title', $data)) {
                $attributes['title'] = $this->title((string) $data['title']);
            }
            if (array_key_exists('description', $data)) {
                $attributes['description'] = $this->description($data['description']);
            }
            if (array_key_exists('position', $data)) {
                $attributes['position'] = max(0, (int) $data['position']);
            }
            if ($attributes !== []) {
                $group->update($attributes);
            }

            return $group->refresh();
        });
    }

    public function move(int $actorId, WorkGroup $group, ?WorkGroup $parent): WorkGroup
    {
        $this->assertAdmin($actorId);

        return DB::transaction(function () use ($group, $parent): WorkGroup {
            $group = WorkGroup::query()->lockForUpdate()->findOrFail($group->id);
            $project = Project::query()->lockForUpdate()->findOrFail($group->project_id);
            $this->assertProjectMutable($project);
            $parent = $parent ? WorkGroup::query()->lockForUpdate()->findOrFail($parent->id) : null;

            if ($parent && ($parent->project_id !== $group->project_id || ! $parent->isActive())) {
                throw new DomainException('Work Group parent must be an active group in the same Project.');
            }
            if ($parent?->id === $group->id || ($parent && $this->isDescendantOf($parent, $group))) {
                throw new DomainException('Work Group hierarchy may not contain cycles.');
            }
            if (($parent?->depth() ?? 0) + $this->subtreeHeight($group) > self::MAX_DEPTH) {
                throw new DomainException('Moving this Work Group would exceed five levels.');
            }
            if ($group->parent_id === $parent?->id) {
                return $group;
            }

            $group->update(['parent_id' => $parent?->id]);

            return $group->refresh();
        });
    }

    /** @param array<int, int> $orderedIds */
    public function reorder(int $actorId, Project $project, array $orderedIds): void
    {
        $this->assertAdmin($actorId);

        DB::transaction(function () use ($project, $orderedIds): void {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectMutable($project);
            $groups = WorkGroup::query()->where('project_id', $project->id)->whereIn('id', $orderedIds)->lockForUpdate()->get();

            if ($groups->count() !== count(array_unique(array_map('intval', $orderedIds))) || $groups->isEmpty()) {
                throw new DomainException('Work Group order contains invalid groups.');
            }
            if ($groups->pluck('parent_id')->unique()->count() !== 1) {
                throw new DomainException('Only sibling Work Groups may be reordered together.');
            }

            $parentId = $groups->first()->parent_id;
            $expected = WorkGroup::query()->where('project_id', $project->id)->where('parent_id', $parentId)->active()->pluck('id')->sort()->values()->all();
            $actual = collect($orderedIds)->map(fn ($id): int => (int) $id)->sort()->values()->all();
            if ($expected !== $actual) {
                throw new DomainException('Work Group order must contain every active sibling exactly once.');
            }

            foreach ($orderedIds as $index => $id) {
                WorkGroup::query()->whereKey((int) $id)->update(['position' => ($index + 1) * 10]);
            }
        });
    }

    public function inactivate(int $actorId, WorkGroup $group): WorkGroup
    {
        $this->assertAdmin($actorId);

        return DB::transaction(function () use ($group): WorkGroup {
            $group = WorkGroup::query()->lockForUpdate()->findOrFail($group->id);
            $project = Project::query()->lockForUpdate()->findOrFail($group->project_id);
            $this->assertProjectMutable($project);

            if (! $group->isActive()) {
                return $group;
            }
            if ($group->children()->active()->exists()) {
                throw new DomainException('A Work Group with active child groups cannot be inactivated.');
            }

            $group->update(['status' => 'inactive', 'inactivated_at' => now()]);

            return $group->refresh();
        });
    }

    private function resolveParent(Project $project, mixed $parentId): ?WorkGroup
    {
        if (! $parentId) {
            return null;
        }

        $parent = WorkGroup::query()->find((int) $parentId);
        if ($parent === null || $parent->project_id !== $project->id || ! $parent->isActive()) {
            throw new DomainException('Work Group parent must be active and belong to the same Project.');
        }

        return $parent;
    }

    private function isDescendantOf(WorkGroup $candidate, WorkGroup $ancestor): bool
    {
        $current = $candidate;
        $visited = [];

        while ($current !== null) {
            if ($current->id === $ancestor->id) {
                return true;
            }
            if (isset($visited[$current->id])) {
                throw new DomainException('Work Group hierarchy contains a cycle.');
            }
            $visited[$current->id] = true;
            $current = $current->parent;
        }

        return false;
    }

    private function subtreeHeight(WorkGroup $group): int
    {
        $children = $group->children()->get();

        return $children->isEmpty()
            ? 1
            : 1 + $children->map(fn (WorkGroup $child): int => $this->subtreeHeight($child))->max();
    }

    private function assertAdmin(int $actorId): void
    {
        $actor = $this->accounts->find($actorId);

        if ($actor === null || ! $actor->isActive || $actor->role !== UserRole::Admin) {
            throw new DomainException('Only an active Admin may manage Work Groups.');
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
        if ($title === '' || mb_strlen($title) > 255) {
            throw new DomainException('Work Group title is required and may not exceed 255 characters.');
        }

        return $title;
    }

    private function description(mixed $description): ?string
    {
        if (! filled($description)) {
            return null;
        }

        $description = trim((string) $description);
        if (mb_strlen($description) > 2000) {
            throw new DomainException('Work Group description may not exceed 2000 characters.');
        }

        return $description;
    }
}
