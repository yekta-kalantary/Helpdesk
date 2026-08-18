<?php

namespace Modules\Tasks\Application;

use App\Notifications\ResourceChangedNotification;
use App\Support\ActivityRecorder;
use App\Support\NotificationDispatcher;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Projects\Infrastructure\Models\WorkGroup;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskWorkflow
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
        private readonly TaskNotificationRouter $notificationRouter,
    ) {}

    public function createForCustomer(User $actor, Project $project, array $data): Task
    {
        if (! $actor->isCustomer()) {
            throw new DomainException('Only a Customer may use the Customer task creation flow.');
        }

        $this->assertProjectAccess($actor, $project);

        if (filled($data['work_group_id'] ?? null)) {
            throw new DomainException('Customer-created Tasks must be created at the Project root.');
        }

        return $this->createTask($actor, $project, [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? null,
            'project_status_id' => $data['project_status_id'] ?? null,
            'work_group_id' => null,
            'priority' => TaskPriority::Normal,
            'assigned_to' => null,
            'due_date' => null,
        ]);
    }

    public function createForEmployee(User $actor, Project $project, array $data): Task
    {
        if (! $actor->isEmployee()) {
            throw new DomainException('Only an Employee may use the Employee task creation flow.');
        }

        $this->assertProjectAccess($actor, $project);

        if (filled($data['work_group_id'] ?? null)) {
            throw new DomainException('Employee-created Tasks must be created at the Project root.');
        }

        return $this->createTask($actor, $project, [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? null,
            'project_status_id' => $data['project_status_id'] ?? null,
            'work_group_id' => null,
            'priority' => TaskPriority::Normal,
            'assigned_to' => null,
            'due_date' => null,
        ]);
    }

    public function createForAdmin(User $actor, Project $project, array $data): Task
    {
        $this->assertAdmin($actor);

        return $this->createTask($actor, $project, $data);
    }

    public function updateByAdmin(User $actor, Task $task, array $data): Task
    {
        $this->assertAdmin($actor);
        $task->loadMissing(['project.client', 'projectStatus']);
        $this->assertProjectOpen($task->project);

        if ($task->isDone()) {
            throw new DomainException('Done Tasks must be reopened through a status transition before editing.');
        }

        $statusChanged = false;
        $assigneeChanged = false;

        $task = DB::transaction(function () use ($actor, $task, $data, &$statusChanged, &$assigneeChanged): Task {
            $project = Project::query()->lockForUpdate()->findOrFail($task->project_id);
            $task = Task::query()->with('projectStatus')->lockForUpdate()->findOrFail($task->id);
            $task->setRelation('project', $project);
            $this->assertProjectOpen($project);

            $original = [
                'project_status_id' => $task->project_status_id,
                'status_title' => $task->projectStatus->title,
                'status_is_done' => $task->projectStatus->is_done,
                'work_group_id' => $task->work_group_id,
                'priority' => $task->priority,
                'assigned_to' => $task->assigned_to,
                'due_date' => $task->due_date?->toDateString(),
            ];

            $attributes = Arr::only($data, ['title', 'description', 'due_date']);
            if (array_key_exists('priority', $data)) {
                $attributes['priority'] = $this->priority($data['priority']);
            }
            if (array_key_exists('assigned_to', $data)) {
                $attributes['assigned_to'] = $data['assigned_to'] === null || $data['assigned_to'] === ''
                    ? null
                    : (int) $data['assigned_to'];
            }
            if (array_key_exists('work_group_id', $data)) {
                $attributes['work_group_id'] = $this->resolveWorkGroup($project, $data['work_group_id'])?->id;
            }

            $newStatus = null;
            if (array_key_exists('project_status_id', $data)) {
                $newStatus = $this->resolveStatus($project, $data['project_status_id']);
                $attributes['project_status_id'] = $newStatus->id;
                $this->applyCompletionTimestamp($task, $task->projectStatus, $newStatus, $attributes);
            }

            $task->fill($attributes)->save();
            $task->refresh()->load('projectStatus');
            $statusChanged = $original['project_status_id'] !== $task->project_status_id;
            $assigneeChanged = $original['assigned_to'] !== $task->assigned_to;
            $this->recordTaskChanges($actor, $task, $original);

            return $task;
        });

        if ($statusChanged) {
            $this->notifications->send(
                $this->notificationRouter->statusChanged($task),
                $this->notification($task, 'تغییر وضعیت تسک', "وضعیت {$task->reference} تغییر کرد."),
                $actor,
            );
        } elseif ($assigneeChanged) {
            $this->notifications->send(
                $this->notificationRouter->assigneeChanged($task),
                $this->notification($task, 'تغییر مسئول تسک', "مسئول تسک {$task->reference} تغییر کرد."),
                $actor,
            );
        }

        return $task;
    }

    public function changeStatus(User $actor, Task $task, ProjectTaskStatus $status): Task
    {
        $task->loadMissing('project');
        $this->assertProjectAccess($actor, $task->project);

        $changed = false;
        $task = DB::transaction(function () use ($actor, $task, $status, &$changed): Task {
            $project = Project::query()->lockForUpdate()->findOrFail($task->project_id);
            $this->assertProjectOpen($project);
            $task = Task::query()->with('projectStatus')->lockForUpdate()->findOrFail($task->id);
            $target = ProjectTaskStatus::query()->lockForUpdate()->findOrFail($status->id);

            if (! $target->is_active || $target->project_id !== $project->id) {
                throw new DomainException('Target Project Status must be active and belong to the Task Project.');
            }

            $old = $task->projectStatus;
            if ($old->id === $target->id) {
                return $task;
            }

            $attributes = ['project_status_id' => $target->id];
            $this->applyCompletionTimestamp($task, $old, $target, $attributes);
            $task->fill($attributes)->save();
            $task->refresh()->load('projectStatus');
            $changed = true;

            $this->recordStatusActivity($actor, $task, $old, $target);

            return $task;
        });

        if ($changed) {
            $this->notifications->send(
                $this->notificationRouter->statusChanged($task),
                $this->notification($task, 'تغییر وضعیت تسک', "وضعیت {$task->reference} تغییر کرد."),
                $actor,
            );
        }

        return $task;
    }

    public function transitionByCustomer(User $actor, Task $task, ProjectTaskStatus $status): Task
    {
        if (! $actor->isCustomer()) {
            throw new DomainException('Only a Customer may use the Customer transition flow.');
        }

        return $this->changeStatus($actor, $task, $status);
    }

    public function transitionByEmployee(User $actor, Task $task, ProjectTaskStatus $status): Task
    {
        if (! $actor->isEmployee()) {
            throw new DomainException('Only an Employee may use the Employee transition flow.');
        }

        return $this->changeStatus($actor, $task, $status);
    }

    private function createTask(User $actor, Project $project, array $data): Task
    {
        $task = DB::transaction(function () use ($actor, $project, $data): Task {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectOpen($project);
            $status = $this->resolveStatus($project, $data['project_status_id'] ?? null);
            $workGroup = $this->resolveWorkGroup($project, $data['work_group_id'] ?? null);
            $priority = $this->priority($data['priority'] ?? TaskPriority::Normal);
            $title = trim((string) ($data['title'] ?? ''));

            if ($title === '') {
                throw new DomainException('Task title is required.');
            }

            $task = Task::query()->create([
                'project_id' => $project->id,
                'project_status_id' => $status->id,
                'work_group_id' => $workGroup?->id,
                'created_by' => $actor->id,
                'assigned_to' => isset($data['assigned_to']) && $data['assigned_to'] !== '' ? (int) $data['assigned_to'] : null,
                'title' => $title,
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'priority' => $priority,
                'due_date' => $data['due_date'] ?? null,
                'completed_at' => $status->is_done ? now() : null,
            ]);

            $this->activities->record($actor, 'task.created', $project, $task, [
                'reference' => $task->reference,
                'project_status_id' => $status->id,
                'project_status_title_snapshot' => $status->title,
                'priority' => $task->priority->value,
                'assigned_to' => $task->assigned_to,
                'work_group_id' => $task->work_group_id,
                'work_group_title_snapshot' => $workGroup?->title,
            ]);

            return $task->load('projectStatus');
        });

        $this->notifications->send(
            $this->notificationRouter->created($task),
            $this->notification($task, 'تسک جدید', "تسک {$task->reference} ایجاد شد."),
            $actor,
        );

        return $task;
    }

    private function resolveStatus(Project $project, mixed $statusId): ProjectTaskStatus
    {
        $status = $statusId
            ? ProjectTaskStatus::query()->find((int) $statusId)
            : $project->defaultOpenTaskStatus();

        if (! $status || ! $status->is_active || $status->project_id !== $project->id) {
            throw new DomainException('Task Project Status must be active and belong to the same Project.');
        }

        return $status;
    }

    private function resolveWorkGroup(Project $project, mixed $workGroupId): ?WorkGroup
    {
        if (! $workGroupId) {
            return null;
        }

        $group = WorkGroup::query()->find((int) $workGroupId);
        if (! $group || ! $group->isActive() || $group->project_id !== $project->id) {
            throw new DomainException('Task Work Group must be active and belong to the same Project.');
        }

        return $group;
    }

    private function applyCompletionTimestamp(Task $task, ProjectTaskStatus $old, ProjectTaskStatus $new, array &$attributes): void
    {
        if (! $old->is_done && $new->is_done) {
            $attributes['completed_at'] = now();
        } elseif ($old->is_done && ! $new->is_done) {
            $attributes['completed_at'] = null;
        }
    }

    private function recordTaskChanges(User $actor, Task $task, array $original): void
    {
        if ($original['assigned_to'] !== $task->assigned_to) {
            $this->activities->record($actor, 'task.assignee_changed', $task->project, $task, [
                'old' => $original['assigned_to'],
                'new' => $task->assigned_to,
            ]);
        }

        if ($original['project_status_id'] !== $task->project_status_id) {
            $old = ProjectTaskStatus::query()->findOrFail($original['project_status_id']);
            $this->recordStatusActivity($actor, $task, $old, $task->projectStatus);
        }

        if ($original['work_group_id'] !== $task->work_group_id) {
            $oldWorkGroup = $original['work_group_id']
                ? WorkGroup::query()->find($original['work_group_id'])
                : null;
            $newWorkGroup = $task->work_group_id
                ? WorkGroup::query()->find($task->work_group_id)
                : null;

            $this->activities->record($actor, 'task.work_group_changed', $task->project, $task, [
                'old_work_group_id' => $original['work_group_id'],
                'old_work_group_title_snapshot' => $oldWorkGroup?->title,
                'new_work_group_id' => $task->work_group_id,
                'new_work_group_title_snapshot' => $newWorkGroup?->title,
            ]);
        }

        if ($original['priority'] !== $task->priority) {
            $this->activities->record($actor, 'task.priority_changed', $task->project, $task, [
                'old' => $original['priority']->value,
                'new' => $task->priority->value,
            ]);
        }

        $newDueDate = $task->due_date?->toDateString();
        if ($original['due_date'] !== $newDueDate) {
            $this->activities->record($actor, 'task.due_date_changed', $task->project, $task, [
                'old' => $original['due_date'],
                'new' => $newDueDate,
            ]);
        }
    }

    private function recordStatusActivity(User $actor, Task $task, ProjectTaskStatus $old, ProjectTaskStatus $new): void
    {
        $this->activities->record($actor, 'task.status_changed', $task->project, $task, [
            'previous_status_id' => $old->id,
            'previous_status_title_snapshot' => $old->title,
            'new_status_id' => $new->id,
            'new_status_title_snapshot' => $new->title,
        ]);

        if (! $old->is_done && $new->is_done) {
            $this->activities->record($actor, 'task.completed', $task->project, $task);
        } elseif ($old->is_done && ! $new->is_done) {
            $this->activities->record($actor, 'task.reopened', $task->project, $task);
        }
    }

    private function notification(Task $task, string $title, string $body): ResourceChangedNotification
    {
        return new ResourceChangedNotification(
            $title,
            $body,
            route('tasks.show', $task),
            [
                'resource_type' => 'task',
                'resource_id' => $task->id,
                'reference' => $task->reference,
            ],
        );
    }

    private function assertAdmin(User $user): void
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            throw new DomainException('Only an active Admin may perform this action.');
        }
    }

    private function assertProjectAccess(User $user, Project $project): void
    {
        if (! $user->is_active || ! $user->canAuthenticate()) {
            throw new DomainException('An active account is required.');
        }

        if ($user->isCustomer()) {
            if ($user->client_id !== $project->client_id || ! $project->hasActiveMember($user)) {
                throw new DomainException('Active Project membership is required.');
            }
        } elseif ($user->isEmployee()) {
            if (! $project->hasActiveMember($user)) {
                throw new DomainException('Active Project membership is required.');
            }
        } elseif (! $user->isAdmin()) {
            throw new DomainException('Project access is not allowed.');
        }

        $this->assertProjectOpen($project);
    }

    private function assertProjectOpen(Project $project): void
    {
        $project->loadMissing('client');
        if (! $project->isActive() || ! $project->client->isActive()) {
            throw new DomainException('The Project is read-only.');
        }
    }

    private function priority(TaskPriority|string $priority): TaskPriority
    {
        return $priority instanceof TaskPriority ? $priority : TaskPriority::from($priority);
    }
}
