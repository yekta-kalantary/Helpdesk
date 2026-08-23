<?php

namespace Modules\Tasks\Application;

use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Audit\Application\ActivityRecorder;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Notifications\Application\Contracts\ResourceChangedNotificationFactory;
use Modules\Notifications\Application\NotificationDispatcher;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Projects\Application\DTOs\ProjectSummary;
use Modules\Projects\Application\DTOs\ProjectTaskStatusSummary;
use Modules\Projects\Application\DTOs\WorkGroupSummary;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskWorkflow
{
    public function __construct(
        private readonly AccountDirectory $accounts,
        private readonly AccountAuthenticationEligibility $eligibility,
        private readonly ProjectMembershipDirectory $projects,
        private readonly ClientStatusQuery $clients,
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
        private readonly TaskNotificationRouter $notificationRouter,
        private readonly ResourceChangedNotificationFactory $notificationFactory,
    ) {}

    public function createForCustomer(object $actor, object $project, array $data): Task
    {
        return $this->createForRole($actor, $project, $data, UserRole::Customer);
    }

    public function createForEmployee(object $actor, object $project, array $data): Task
    {
        return $this->createForRole($actor, $project, $data, UserRole::Employee);
    }

    public function createForAdmin(object $actor, object $project, array $data): Task
    {
        $this->assertAdmin((int) $actor->id);

        return $this->createTask((int) $actor->id, (int) $project->id, $data);
    }

    public function updateByAdmin(object $actor, Task $task, array $data): Task
    {
        $actorId = (int) $actor->id;
        $this->assertAdmin($actorId);

        return DB::transaction(function () use ($actorId, $task, $data): Task {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertProjectOpen($this->project($task->project_id));
            $oldStatus = $this->status((int) $task->project_status_id, (int) $task->project_id);

            if ($oldStatus->isDone) {
                throw new DomainException('Done Tasks must be reopened through a status transition before editing.');
            }

            $original = [
                'project_status_id' => (int) $task->project_status_id,
                'work_group_id' => $task->work_group_id !== null ? (int) $task->work_group_id : null,
                'priority' => $task->priority,
                'assigned_to' => $task->assigned_to !== null ? (int) $task->assigned_to : null,
                'due_date' => $task->due_date?->toDateString(),
            ];

            $attributes = Arr::only($data, ['title', 'description', 'due_date']);
            if (array_key_exists('priority', $data)) {
                $attributes['priority'] = $this->priority($data['priority']);
            }
            if (array_key_exists('assigned_to', $data)) {
                $attributes['assigned_to'] = filled($data['assigned_to']) ? (int) $data['assigned_to'] : null;
            }
            if (array_key_exists('work_group_id', $data)) {
                $attributes['work_group_id'] = $this->workGroup($data['work_group_id'], (int) $task->project_id)?->id;
            }
            if (array_key_exists('project_status_id', $data)) {
                $newStatus = $this->status((int) $data['project_status_id'], (int) $task->project_id);
                $attributes['project_status_id'] = $newStatus->id;
                $attributes['completed_at'] = $newStatus->isDone ? now() : null;
            }

            $this->assertAssignee((int) $task->project_id, $attributes['assigned_to'] ?? $original['assigned_to']);
            $task->fill($attributes)->save();
            $task->refresh();
            $this->recordTaskChanges($actorId, $task, $original);

            return $task;
        });
    }

    public function changeStatus(object $actor, Task $task, object $status): Task
    {
        $actorId = (int) $actor->id;
        $projectId = (int) $task->project_id;
        $this->assertProjectAccess($actorId, $projectId);

        return DB::transaction(function () use ($actorId, $task, $status): Task {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $projectId = (int) $task->project_id;
            $this->assertProjectOpen($this->project($projectId));
            $target = $this->status((int) $status->id, $projectId);
            $old = $this->status((int) $task->project_status_id, $projectId);

            if ($old->id === $target->id) {
                return $task;
            }

            $task->update(['project_status_id' => $target->id, 'completed_at' => $target->isDone ? now() : null]);
            $task->refresh();
            $this->recordStatusActivity($actorId, $task, $old, $target);

            return $task;
        });
    }

    public function transitionByCustomer(object $actor, Task $task, object $status): Task
    {
        $this->assertRole((int) $actor->id, UserRole::Customer, 'Customer transition');

        return $this->changeStatus($actor, $task, $status);
    }

    public function transitionByEmployee(object $actor, Task $task, object $status): Task
    {
        $this->assertRole((int) $actor->id, UserRole::Employee, 'Employee transition');

        return $this->changeStatus($actor, $task, $status);
    }

    private function createForRole(object $actor, object $project, array $data, UserRole $role): Task
    {
        $this->assertRole((int) $actor->id, $role, $role->value.' task creation');
        if (filled($data['work_group_id'] ?? null)) {
            throw new DomainException($role->value.'-created Tasks must be created at the Project root.');
        }

        $this->assertProjectAccess((int) $actor->id, (int) $project->id);

        return $this->createTask((int) $actor->id, (int) $project->id, [
            'title' => $data['title'] ?? '', 'description' => $data['description'] ?? null,
            'project_status_id' => $data['project_status_id'] ?? null, 'work_group_id' => null,
            'priority' => TaskPriority::Normal, 'assigned_to' => null, 'due_date' => null,
        ]);
    }

    private function createTask(int $actorId, int $projectId, array $data): Task
    {
        $task = DB::transaction(function () use ($actorId, $projectId, $data): Task {
            $this->assertProjectOpen($this->project($projectId));
            $status = $data['project_status_id'] ? $this->status((int) $data['project_status_id'], $projectId) : $this->defaultStatus($projectId);
            $workGroup = $this->workGroup($data['work_group_id'] ?? null, $projectId);
            $title = trim((string) ($data['title'] ?? ''));
            if ($title === '') {
                throw new DomainException('Task title is required.');
            }
            $assigneeId = filled($data['assigned_to'] ?? null) ? (int) $data['assigned_to'] : null;
            $this->assertAssignee($projectId, $assigneeId);

            $task = Task::query()->create([
                'project_id' => $projectId, 'project_status_id' => $status->id, 'work_group_id' => $workGroup?->id,
                'created_by' => $actorId, 'assigned_to' => $assigneeId, 'title' => $title,
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'priority' => $this->priority($data['priority'] ?? TaskPriority::Normal), 'due_date' => $data['due_date'] ?? null,
                'completed_at' => $status->isDone ? now() : null,
            ]);

            $this->activities->recordIds($actorId, 'task.created', $projectId, $task->id, [
                'reference' => $task->reference,
                'project_status_id' => $status->id,
                'priority' => $task->priority->value,
                'assigned_to' => $task->assigned_to,
                'work_group_id' => $task->work_group_id,
            ]);

            return $task;
        });

        $this->notifications->sendToAccountIds(
            $this->notificationRouter->created($task),
            $this->notificationFactory->make(
                'تسک جدید',
                "تسک {$task->reference} ایجاد شد.",
                route('tasks.show', $task),
                [
                    'resource_type' => 'task',
                    'resource_id' => $task->id,
                    'reference' => $task->reference,
                ],
            ),
            (int) $task->created_by,
        );

        return $task;
    }

    private function recordTaskChanges(int $actorId, Task $task, array $original): void
    {
        $projectId = (int) $task->project_id;

        if ($original['assigned_to'] !== $task->assigned_to) {
            $this->activities->recordIds($actorId, 'task.assignee_changed', $projectId, $task->id, [
                'old' => $original['assigned_to'],
                'new' => $task->assigned_to !== null ? (int) $task->assigned_to : null,
            ]);
        }

        if ($original['project_status_id'] !== (int) $task->project_status_id) {
            $old = $this->status($original['project_status_id'], $projectId);
            $new = $this->status((int) $task->project_status_id, $projectId);
            $this->recordStatusActivity($actorId, $task, $old, $new);
        }

        if ($original['work_group_id'] !== ($task->work_group_id !== null ? (int) $task->work_group_id : null)) {
            $this->activities->recordIds($actorId, 'task.work_group_changed', $projectId, $task->id, [
                'old_work_group_id' => $original['work_group_id'],
                'new_work_group_id' => $task->work_group_id !== null ? (int) $task->work_group_id : null,
            ]);
        }

        if ($original['priority'] !== $task->priority) {
            $this->activities->recordIds($actorId, 'task.priority_changed', $projectId, $task->id, [
                'old' => $original['priority']->value,
                'new' => $task->priority->value,
            ]);
        }

        $newDueDate = $task->due_date?->toDateString();
        if ($original['due_date'] !== $newDueDate) {
            $this->activities->recordIds($actorId, 'task.due_date_changed', $projectId, $task->id, [
                'old' => $original['due_date'],
                'new' => $newDueDate,
            ]);
        }

        if ($original['assigned_to'] !== $task->assigned_to) {
            $this->notifications->sendToAccountIds(
                $this->notificationRouter->assigneeChanged($task),
                $this->notificationFactory->make(
                    'تغییر مسئول تسک',
                    "مسئول تسک {$task->reference} تغییر کرد.",
                    route('tasks.show', $task),
                    [
                        'resource_type' => 'task',
                        'resource_id' => $task->id,
                        'reference' => $task->reference,
                    ],
                ),
                $actorId,
            );
        }
    }

    private function recordStatusActivity(int $actorId, Task $task, ProjectTaskStatusSummary $old, ProjectTaskStatusSummary $new): void
    {
        $projectId = (int) $task->project_id;

        $this->activities->recordIds($actorId, 'task.status_changed', $projectId, $task->id, [
            'previous_status_id' => $old->id,
            'new_status_id' => $new->id,
        ]);

        if (! $old->isDone && $new->isDone) {
            $this->activities->recordIds($actorId, 'task.completed', $projectId, $task->id);
        } elseif ($old->isDone && ! $new->isDone) {
            $this->activities->recordIds($actorId, 'task.reopened', $projectId, $task->id);
        }

        $this->notifications->sendToAccountIds(
            $this->notificationRouter->statusChanged($task),
            $this->notificationFactory->make(
                'تغییر وضعیت تسک',
                "وضعیت {$task->reference} تغییر کرد.",
                route('tasks.show', $task),
                [
                    'resource_type' => 'task',
                    'resource_id' => $task->id,
                    'reference' => $task->reference,
                ],
            ),
            $actorId,
        );
    }

    private function assertProjectAccess(int $accountId, int $projectId): void
    {
        $account = $this->account($accountId);
        $project = $this->project($projectId);
        if ($account->role !== UserRole::Admin && ! $this->projects->hasActiveMembership($projectId, $accountId)) {
            throw new DomainException('Active Project membership is required.');
        }
        if ($account->role === UserRole::Customer && $account->clientId !== $project->clientId) {
            throw new DomainException('Active Project membership is required.');
        }
        $this->assertProjectOpen($project);
    }

    private function assertAssignee(int $projectId, ?int $accountId): void
    {
        if ($accountId === null) {
            return;
        }
        $account = $this->account($accountId);
        if ($account->role !== UserRole::Admin && ! $this->projects->hasActiveMembership($projectId, $accountId)) {
            throw new DomainException('Task assignee must be an active member of the task Project.');
        }
        if ($account->role === UserRole::Customer && $account->clientId !== $this->project($projectId)->clientId) {
            throw new DomainException('Customer assignee must be an active member of the task Project.');
        }
    }

    private function account(int $accountId): object
    {
        $account = $this->accounts->find($accountId);
        if ($account === null || ! $this->eligibility->canAuthenticateAccount($accountId)) {
            throw new DomainException('An active account is required.');
        }

        return $account;
    }

    private function assertAdmin(int $accountId): void
    {
        $this->assertRole($accountId, UserRole::Admin, 'Admin action');
    }

    private function assertRole(int $accountId, UserRole $role, string $action): void
    {
        if ($this->account($accountId)->role !== $role) {
            throw new DomainException('Only an active '.$role->value.' may use the '.$action.' flow.');
        }
    }

    private function project(int $projectId): ProjectSummary
    {
        return $this->projects->findProject($projectId) ?? throw new DomainException('Task Project is required.');
    }

    private function assertProjectOpen(ProjectSummary $project): void
    {
        if (! $project->isActive || $this->clients->find($project->clientId)?->isActive !== true) {
            throw new DomainException('The Project is read-only.');
        }
    }

    private function defaultStatus(int $projectId): ProjectTaskStatusSummary
    {
        return $this->projects->defaultOpenTaskStatus($projectId) ?? throw new DomainException('Task Project Status must be active and belong to the same Project.');
    }

    private function status(int $statusId, int $projectId): ProjectTaskStatusSummary
    {
        $status = $this->projects->findActiveTaskStatus($statusId);
        if ($status === null || $status->projectId !== $projectId) {
            throw new DomainException('Task Project Status must be active and belong to the same Project.');
        }

        return $status;
    }

    private function workGroup(mixed $workGroupId, int $projectId): ?WorkGroupSummary
    {
        if (! filled($workGroupId)) {
            return null;
        }
        $workGroup = $this->projects->findActiveWorkGroup((int) $workGroupId);
        if ($workGroup === null || $workGroup->projectId !== $projectId) {
            throw new DomainException('Task Work Group must be active and belong to the same Project.');
        }

        return $workGroup;
    }

    private function priority(TaskPriority|string $priority): TaskPriority
    {
        return $priority instanceof TaskPriority ? $priority : TaskPriority::from($priority);
    }
}
