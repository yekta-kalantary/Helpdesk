<?php

namespace Modules\Projects\Infrastructure;

use Illuminate\Support\Facades\DB;
use Modules\Projects\Domain\Contracts\ProjectRepository;
use Modules\Projects\Infrastructure\Models\Project;

class EloquentProjectRepository implements ProjectRepository
{
    public function search(?string $term = null, ?int $userId = null, bool $isAdmin = false): array
    {
        return Project::query()
            ->when(! $isAdmin, fn ($query) => $query->whereExists(fn ($memberQuery) => $memberQuery
                ->selectRaw('1')
                ->from('project_user')
                ->whereColumn('project_user.project_id', 'projects.id')
                ->where('project_user.user_id', $userId)))
            ->when($term, fn ($query) => $query->where('title', 'like', "%{$term}%"))
            ->withCount('members')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'members_count' => $project->members_count,
            ])
            ->all();
    }

    public function find(int $id): array
    {
        $project = Project::query()->findOrFail($id);

        return [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'member_ids' => $project->members()->pluck('users.id')->all(),
        ];
    }

    public function create(array $attributes, array $memberIds): int
    {
        return DB::transaction(function () use ($attributes, $memberIds): int {
            $project = Project::query()->create($attributes);
            $project->members()->sync($memberIds);

            return $project->id;
        });
    }

    public function update(int $id, array $attributes, array $memberIds): void
    {
        DB::transaction(function () use ($id, $attributes, $memberIds): void {
            $project = Project::query()->findOrFail($id);
            $project->update($attributes);
            $project->members()->sync($memberIds);
        });
    }

    public function delete(int $id): void
    {
        Project::query()->findOrFail($id)->delete();
    }
}
