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
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
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
        $this->assertCustomerProjectAccess($actor, $project);

        $task = DB::transaction(function () use ($actor, $project, $data): Task {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectOpen($project);

            $task = Task::query()->create([
                'project_id' => $project->id,
                'created_by' => $actor->id,
                'assigned_to' => null,
                'title' => trim((string) $data['title']),
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'status' => TaskStatus::WaitingAdmin,
                'priority' => TaskPriority::Normal,
                'due_date' => null,
                'completed_at' => null,
            ]);

            $this->activities->record($actor, 'task.created', $project, $task, [
                'reference' => $task->reference,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
            ]);

            $this->notifications->send(
                $this->notificationRouter->created($task),
                $this->notification($task, 'درخواست جدید مشتری', "تسک {$task->reference} وارد صف ادمین شد."),
                $actor,
            );

            return $task;
        });

        return $task;
    }

    public function createForAdmin(User $actor, Project $project, array $data): Task
    {
        $this->assertAdmin($actor);
        $this->assertProjectOpen($project);

        $status = $this->status($data['status'] ?? TaskStatus::WaitingAdmin);
        $assignedTo = isset($data['assigned_to']) ? (int) $data['assigned_to'] : null;
        $assignedTo = $this->normalizeWaitingAdminAssignee($status, $assignedTo);

        $task = DB::transaction(function () use ($actor, $project, $data, $status, $assignedTo): Task {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $this->assertProjectOpen($project);

            $task = Task::query()->create([
                'project_id' => $project->id,
                'created_by' => $actor->id,
                'assigned_to' => $assignedTo,
                'title' => trim((string) $data['title']),
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'status' => $status,
                'priority' => $this->priority($data['priority'] ?? TaskPriority::Normal),
                'due_date' => $data['due_date'] ?? null,
                'completed_at' => $status === TaskStatus::Completed ? now() : null,
            ]);

            $this->activities->record($actor, 'task.created', $project, $task, [
                'reference' => $task->reference,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'assigned_to' => $task->assigned_to,
            ]);

            $this->notifications->send(
                $this->notificationRouter->created($task),
                $this->notification($task, 'تسک جدید', "تسک {$task->reference} ایجاد شد."),
                $actor,
            );

            return $task;
        });

        return $task;
    }

    public function updateByAdmin(User $actor, Task $task, array $data): Task
    {
        $this->assertAdmin($actor);
        $task->loadMissing('project');
        $this->assertProjectOpen($task->project);

        $original = [];

        $task = DB::transaction(function () use ($actor, $task, $data, &$original): Task {
            $project = Project::query()->lockForUpdate()->findOrFail($task->project_id);
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $task->setRelation('project', $project);
            $this->assertProjectOpen($project);

            $original = [
                'status' => $task->status,
                'priority' => $task->priority,
                'assigned_to' => $task->assigned_to,
                'due_date' => $task->due_date?->toDateString(),
                'completed_at' => $task->completed_at,
            ];

            $status = array_key_exists('status', $data)
                ? $this->status($data['status'])
                : $task->status;

            $assignedTo = array_key_exists('assigned_to', $data)
                ? ($data['assigned_to'] === null || $data['assigned_to'] === '' ? null : (int) $data['assigned_to'])
                : $task->assigned_to;

            $assignedTo = $this->normalizeWaitingAdminAssignee($status, $assignedTo);

            $attributes = Arr::only($data, ['title', 'description', 'due_date']);
            $attributes['status'] = $status;
            $attributes['assigned_to'] = $assignedTo;

            if (array_key_exists('priority', $data)) {
                $attributes['priority'] = $this->priority($data['priority']);
            }

            if ($status === TaskStatus::Completed) {
                $attributes['completed_at'] = $task->completed_at ?? now();
            } elseif ($task->status === TaskStatus::Completed || $task->completed_at !== null) {
                $attributes['completed_at'] = null;
            }

            $task->fill($attributes)->save();
            $task->refresh();
            $this->recordTaskChanges($actor, $task, $original);

            return $task;
        });

        if ($original['status'] !== $task->status) {
            $this->notifications->send(
                $this->notificationRouter->statusChanged($task),
                $this->notification($task, 'تغییر وضعیت تسک', "وضعیت {$task->reference} تغییر کرد."),
                $actor,
            );
        } elseif ($original['assigned_to'] !== $task->assigned_to) {
            $this->notifications->send(
                $this->notificationRouter->assigneeChanged($task),
                $this->notification($task, 'تغییر مسئول تسک', "مسئول تسک {$task->reference} تغییر کرد."),
                $actor,
            );
        }

        return $task;
    }

    public function transitionByCustomer(User $actor, Task $task, TaskStatus $status): Task
    {
        $task->loadMissing('project');
        $this->assertCustomerProjectAccess($actor, $task->project);

        if ($task->isTerminal()) {
            throw new DomainException('Terminal tasks are read-only for customers.');
        }

        if ($task->assigned_to !== $actor->id) {
            throw new DomainException('Only the assigned customer may change this task status.');
        }

        if (! in_array($status, [
            TaskStatus::Todo,
            TaskStatus::InProgress,
            TaskStatus::WaitingAdmin,
            TaskStatus::Completed,
        ], true)) {
            throw new DomainException('Customer transition is not allowed.');
        }

        $oldStatus = null;
        $oldAssignee = null;

        $task = DB::transaction(function () use ($actor, $task, $status, &$oldStatus, &$oldAssignee): Task {
            $project = Project::query()->lockForUpdate()->findOrFail($task->project_id);
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $task->setRelation('project', $project);
            $this->assertProjectOpen($project);

            $oldStatus = $task->status;
            $oldAssignee = $task->assigned_to;

            $task->status = $status;

            if ($status === TaskStatus::WaitingAdmin) {
                $task->assigned_to = null;
            }

            if ($status === TaskStatus::Completed) {
                $task->completed_at = now();
            }

            $task->save();
            $task->refresh();

            $this->activities->record($actor, 'task.status_changed', $task->project, $task, [
                'old' => $oldStatus->value,
                'new' => $task->status->value,
            ]);

            if ($oldAssignee !== $task->assigned_to) {
                $this->activities->record($actor, 'task.assignee_changed', $task->project, $task, [
                    'old' => $oldAssignee,
                    'new' => $task->assigned_to,
                ]);
            }

            if ($task->status === TaskStatus::Completed) {
                $this->activities->record($actor, 'task.completed', $task->project, $task);
            }

            return $task;
        });

        $this->notifications->send(
            $this->notificationRouter->statusChanged($task),
            $this->notification($task, 'تغییر وضعیت تسک', "وضعیت {$task->reference} تغییر کرد."),
            $actor,
        );

        return $task;
    }

    private function recordTaskChanges(User $actor, Task $task, array $original): void
    {
        if ($original['assigned_to'] !== $task->assigned_to) {
            $this->activities->record($actor, 'task.assignee_changed', $task->project, $task, [
                'old' => $original['assigned_to'],
                'new' => $task->assigned_to,
            ]);
        }

        if ($original['status'] !== $task->status) {
            $this->activities->record($actor, 'task.status_changed', $task->project, $task, [
                'old' => $original['status']->value,
                'new' => $task->status->value,
            ]);

            if ($task->status === TaskStatus::Completed) {
                $this->activities->record($actor, 'task.completed', $task->project, $task);
            }

            if ($task->status === TaskStatus::Cancelled) {
                $this->activities->record($actor, 'task.cancelled', $task->project, $task);
            }

            if ($original['status']->isTerminal() && ! $task->status->isTerminal()) {
                $this->activities->record($actor, 'task.reopened', $task->project, $task);
            }
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

    private function notification(Task $task, string $title, string $body): ResourceChangedNotification
    {
        return new ResourceChangedNotification(
            $title,
            $body,
            url('/tasks/'.$task->id),
            [
                'resource_type' => 'task',
                'resource_id' => $task->id,
                'reference' => $task->reference,
            ],
        );
    }

    private function normalizeWaitingAdminAssignee(TaskStatus $status, ?int $assignedTo): ?int
    {
        if ($status !== TaskStatus::WaitingAdmin || ! $assignedTo) {
            return $assignedTo;
        }

        $user = User::query()->find($assignedTo);

        return $user?->isCustomer() ? null : $assignedTo;
    }

    private function assertAdmin(User $user): void
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            throw new DomainException('Only an active admin may perform this action.');
        }
    }

    private function assertCustomerProjectAccess(User $user, Project $project): void
    {
        if (! $user->isCustomer() || ! $user->canAuthenticate()) {
            throw new DomainException('An active customer account is required.');
        }

        if ($user->client_id !== $project->client_id || ! $project->hasActiveMember($user)) {
            throw new DomainException('Active project membership is required.');
        }

        $this->assertProjectOpen($project);
    }

    private function assertProjectOpen(Project $project): void
    {
        if (! $project->isActive() || ! $project->client()->active()->exists()) {
            throw new DomainException('The project is read-only.');
        }
    }

    private function status(TaskStatus|string $status): TaskStatus
    {
        return $status instanceof TaskStatus ? $status : TaskStatus::from($status);
    }

    private function priority(TaskPriority|string $priority): TaskPriority
    {
        return $priority instanceof TaskPriority ? $priority : TaskPriority::from($priority);
    }
}
