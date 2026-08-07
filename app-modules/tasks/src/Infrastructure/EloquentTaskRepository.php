<?php

namespace Modules\Tasks\Infrastructure;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskComment;

class EloquentTaskRepository implements TaskRepository
{
    public function search(array $scope, ?int $projectId = null, ?string $term = null): array
    {
        $query = DB::table('tasks')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->leftJoin('customers', 'customers.id', '=', 'projects.customer_id')
            ->leftJoin('users as assignees', 'assignees.id', '=', 'tasks.assigned_to')
            ->whereNull('tasks.deleted_at')
            ->whereNull('projects.deleted_at')
            ->select([
                'tasks.id', 'tasks.project_id', 'tasks.title', 'tasks.priority', 'tasks.status', 'tasks.due_at',
                'tasks.assigned_to', 'projects.title as project_title', 'customers.name as customer_name',
                'assignees.name as assignee_name',
            ]);

        $this->applyDbScope($query, $scope);

        return $query
            ->when($projectId, fn ($builder) => $builder->where('tasks.project_id', $projectId))
            ->when($term, fn ($builder) => $builder->where('tasks.title', 'like', "%{$term}%"))
            ->orderBy('tasks.due_at')
            ->orderByDesc('tasks.id')
            ->get()
            ->map(fn (object $row) => (array) $row)
            ->all();
    }

    public function findAccessible(int $id, array $scope): array
    {
        $query = Task::query()
            ->with(['assignee:id,name', 'creator:id,name', 'comments.user:id,name'])
            ->whereKey($id);

        $this->applyEloquentScope($query, $scope);

        $task = $query->firstOrFail();
        $project = DB::table('projects')
            ->leftJoin('customers', 'customers.id', '=', 'projects.customer_id')
            ->where('projects.id', $task->project_id)
            ->first(['projects.title as project_title', 'projects.customer_id', 'customers.name as customer_name']);

        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'project_title' => $project?->project_title,
            'customer_id' => $project?->customer_id,
            'customer_name' => $project?->customer_name,
            'title' => $task->title,
            'description' => $task->description,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->name,
            'creator_name' => $task->creator?->name,
            'priority' => $task->priority->value,
            'status' => $task->status->value,
            'due_at' => $task->due_at?->format('Y-m-d\TH:i'),
            'estimated_minutes' => $task->estimated_minutes,
            'spent_minutes' => $task->spent_minutes,
            'comments' => $task->comments->map(fn (TaskComment $comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user_name' => $comment->user?->name,
                'created_at' => $comment->created_at,
            ])->all(),
            'attachments' => $task->getMedia('attachments')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->file_name,
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ])->all(),
        ];
    }

    public function create(array $attributes): int
    {
        return Task::create($attributes)->id;
    }

    public function update(int $id, array $attributes): void
    {
        Task::query()->findOrFail($id)->update($attributes);
    }

    public function delete(int $id): void
    {
        Task::query()->findOrFail($id)->delete();
    }

    public function addComment(int $taskId, int $userId, string $body): void
    {
        TaskComment::create(['task_id' => $taskId, 'user_id' => $userId, 'body' => $body]);
    }

    private function applyDbScope($query, array $scope): void
    {
        if ($scope['customer_id']) {
            $query->where('projects.customer_id', $scope['customer_id']);
            return;
        }

        if (! $scope['manage_all']) {
            $query->where(function ($nested) use ($scope): void {
                $nested->where('tasks.assigned_to', $scope['actor_id'])
                    ->orWhereExists(fn ($sub) => $sub->selectRaw('1')->from('project_user')
                        ->whereColumn('project_user.project_id', 'tasks.project_id')
                        ->where('project_user.user_id', $scope['actor_id']));
            });
        }
    }

    private function applyEloquentScope(EloquentBuilder $query, array $scope): void
    {
        if ($scope['customer_id']) {
            $query->whereIn('project_id', DB::table('projects')->where('customer_id', $scope['customer_id'])->whereNull('deleted_at')->select('id'));
            return;
        }

        if (! $scope['manage_all']) {
            $query->where(function (EloquentBuilder $nested) use ($scope): void {
                $nested->where('assigned_to', $scope['actor_id'])
                    ->orWhereIn('project_id', DB::table('project_user')->where('user_id', $scope['actor_id'])->select('project_id'));
            });
        }
    }
}
