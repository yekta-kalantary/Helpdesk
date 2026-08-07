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
            ->leftJoin('people as customer_people', 'customer_people.id', '=', 'customers.person_id')
            ->leftJoin('users as assignees', 'assignees.id', '=', 'tasks.assigned_to')
            ->leftJoin('people as assignee_people', 'assignee_people.id', '=', 'assignees.person_id')
            ->whereNull('tasks.deleted_at')
            ->whereNull('projects.deleted_at')
            ->select([
                'tasks.id', 'tasks.project_id', 'tasks.title', 'tasks.priority', 'tasks.status', 'tasks.due_at',
                'tasks.is_customer_visible', 'tasks.assigned_to', 'projects.title as project_title',
                'customer_people.first_name as customer_first_name', 'customer_people.last_name as customer_last_name',
                'assignee_people.first_name as assignee_first_name', 'assignee_people.last_name as assignee_last_name',
            ]);

        $this->applyDbScope($query, $scope);

        return $query
            ->when($projectId, fn ($builder) => $builder->where('tasks.project_id', $projectId))
            ->when($term, fn ($builder) => $builder->where('tasks.title', 'like', "%{$term}%"))
            ->orderBy('tasks.due_at')
            ->orderByDesc('tasks.id')
            ->get()
            ->map(fn (object $row) => [
                'id' => $row->id,
                'project_id' => $row->project_id,
                'title' => $row->title,
                'priority' => $row->priority,
                'status' => $row->status,
                'due_at' => $row->due_at,
                'is_customer_visible' => (bool) $row->is_customer_visible,
                'assigned_to' => $row->assigned_to,
                'project_title' => $row->project_title,
                'customer_name' => trim(($row->customer_first_name ?? '').' '.($row->customer_last_name ?? '')),
                'assignee_name' => $row->assigned_to ? trim(($row->assignee_first_name ?? '').' '.($row->assignee_last_name ?? '')) : null,
            ])
            ->all();
    }

    public function findAccessible(int $id, array $scope): array
    {
        $query = Task::query()
            ->with(['assignee.person', 'creator.person', 'comments.user.person'])
            ->whereKey($id)
            ->whereIn('project_id', DB::table('projects')->whereNull('deleted_at')->select('id'));

        $this->applyEloquentScope($query, $scope);

        $task = $query->firstOrFail();
        $project = DB::table('projects')
            ->leftJoin('customers', 'customers.id', '=', 'projects.customer_id')
            ->leftJoin('people as customer_people', 'customer_people.id', '=', 'customers.person_id')
            ->where('projects.id', $task->project_id)
            ->first([
                'projects.title as project_title', 'projects.customer_id',
                'customer_people.first_name as customer_first_name', 'customer_people.last_name as customer_last_name',
            ]);

        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'project_title' => $project?->project_title,
            'customer_id' => $project?->customer_id,
            'customer_name' => $project ? trim(($project->customer_first_name ?? '').' '.($project->customer_last_name ?? '')) : null,
            'title' => $task->title,
            'description' => $task->description,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->full_name,
            'creator_name' => $task->creator?->full_name,
            'priority' => $task->priority->value,
            'status' => $task->status->value,
            'is_customer_visible' => (bool) $task->is_customer_visible,
            'due_at' => $task->due_at?->format('Y-m-d\TH:i'),
            'estimated_minutes' => $task->estimated_minutes,
            'spent_minutes' => $task->spent_minutes,
            'comments' => $task->comments->map(fn (TaskComment $comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user_name' => $comment->user?->full_name,
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
            $query->where('projects.customer_id', $scope['customer_id'])
                ->where('tasks.is_customer_visible', true);

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
            $query->where('is_customer_visible', true)
                ->whereIn('project_id', DB::table('projects')->where('customer_id', $scope['customer_id'])->whereNull('deleted_at')->select('id'));

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
