<?php

namespace Modules\Tasks\Infrastructure;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;
use Modules\Media\Domain\Contracts\MediaManager;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskComment;

class EloquentTaskRepository implements TaskRepository
{
    public function __construct(private readonly MediaManager $media) {}

    public function search(array $scope, ?int $projectId = null, ?string $term = null): array
    {
        $query = DB::table('tasks')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->leftJoin('contacts as project_contacts', 'project_contacts.id', '=', 'projects.contact_id')
            ->leftJoin('users as assignees', 'assignees.id', '=', 'tasks.assigned_to')
            ->leftJoin('contacts as assignee_contacts', 'assignee_contacts.id', '=', 'assignees.contact_id')
            ->whereNull('tasks.deleted_at')
            ->whereNull('projects.deleted_at')
            ->select([
                'tasks.id', 'tasks.project_id', 'tasks.title', 'tasks.priority', 'tasks.status', 'tasks.due_at',
                'tasks.assigned_to', 'projects.title as project_title',
                'project_contacts.first_name as contact_first_name', 'project_contacts.last_name as contact_last_name',
                'assignee_contacts.first_name as assignee_first_name', 'assignee_contacts.last_name as assignee_last_name',
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
                'assigned_to' => $row->assigned_to,
                'project_title' => $row->project_title,
                'contact_name' => trim(($row->contact_first_name ?? '').' '.($row->contact_last_name ?? '')) ?: null,
                'assignee_name' => $row->assigned_to ? trim(($row->assignee_first_name ?? '').' '.($row->assignee_last_name ?? '')) : null,
            ])
            ->all();
    }

    public function findAccessible(int $id, array $scope): array
    {
        $query = Task::query()
            ->with(['assignee.contact', 'creator.contact', 'comments.user.contact'])
            ->whereKey($id)
            ->whereIn('project_id', DB::table('projects')->whereNull('deleted_at')->select('id'));

        $this->applyEloquentScope($query, $scope);

        $task = $query->firstOrFail();
        $project = DB::table('projects')
            ->leftJoin('contacts', 'contacts.id', '=', 'projects.contact_id')
            ->where('projects.id', $task->project_id)
            ->first([
                'projects.title as project_title', 'projects.contact_id',
                'contacts.first_name as contact_first_name', 'contacts.last_name as contact_last_name',
            ]);

        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'project_title' => $project?->project_title,
            'contact_id' => $project?->contact_id,
            'contact_name' => $project ? (trim(($project->contact_first_name ?? '').' '.($project->contact_last_name ?? '')) ?: null) : null,
            'title' => $task->title,
            'description' => $task->description,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->full_name,
            'creator_name' => $task->creator?->full_name,
            'priority' => $task->priority->value,
            'status' => $task->status->value,
            'due_at' => $task->due_at?->format('Y-m-d\TH:i'),
            'estimated_minutes' => $task->estimated_minutes,
            'spent_minutes' => $task->spent_minutes,
            'comments' => $task->comments->map(fn (TaskComment $comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user_name' => $comment->user?->full_name,
                'created_at' => $comment->created_at,
            ])->all(),
            'attachments' => $this->media->list(Task::class, $task->id, 'attachments'),
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
        if (! $scope['manage_all']) {
            $query->where(function (EloquentBuilder $nested) use ($scope): void {
                $nested->where('assigned_to', $scope['actor_id'])
                    ->orWhereIn('project_id', DB::table('project_user')->where('user_id', $scope['actor_id'])->select('project_id'));
            });
        }
    }
}
