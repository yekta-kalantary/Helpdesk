<?php

namespace Modules\Tasks\Infrastructure;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Infrastructure\Models\Task;

class EloquentTaskRepository implements TaskRepository
{
    public function search(array $scope, ?int $projectId = null, ?string $term = null): array
    {
        $query = Task::query()
            ->with('project:id,title')
            ->when($projectId, fn (Builder $builder) => $builder->where('project_id', $projectId))
            ->when($term, fn (Builder $builder) => $builder->where('title', 'like', "%{$term}%"));

        $this->applyScope($query, $scope);

        return $query
            ->orderBy('is_done')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'project_id' => $task->project_id,
                'project_title' => $task->project?->title,
                'title' => $task->title,
                'description' => $task->description,
                'is_done' => (bool) $task->is_done,
            ])
            ->all();
    }

    public function findAccessible(int $id, array $scope): array
    {
        $query = Task::query()->whereKey($id);
        $this->applyScope($query, $scope);

        $task = $query->firstOrFail();

        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'title' => $task->title,
            'description' => $task->description,
            'is_done' => (bool) $task->is_done,
        ];
    }

    public function create(array $attributes): int
    {
        return Task::query()->create($attributes)->id;
    }

    public function update(int $id, array $attributes): void
    {
        Task::query()->findOrFail($id)->update($attributes);
    }

    public function delete(int $id): void
    {
        Task::query()->findOrFail($id)->delete();
    }

    private function applyScope(Builder $query, array $scope): void
    {
        if ($scope['manage_all']) {
            return;
        }

        $query->whereIn(
            'project_id',
            DB::table('project_user')
                ->where('user_id', $scope['actor_id'])
                ->select('project_id'),
        );
    }
}
