<?php

namespace Modules\Tasks\Application;

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
    public function createForCustomer(User $actor, Project $project, array $data): Task
    {
        $this->assertCustomerProjectAccess($actor, $project);

        return DB::transaction(fn (): Task => Task::query()->create([
            'project_id' => $project->id,
            'created_by' => $actor->id,
            'assigned_to' => null,
            'title' => trim((string) $data['title']),
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'status' => TaskStatus::WaitingAdmin,
            'priority' => TaskPriority::Normal,
            'due_date' => null,
            'completed_at' => null,
        ]));
    }

    public function createForAdmin(User $actor, Project $project, array $data): Task
    {
        $this->assertAdmin($actor);
        $this->assertProjectOpen($project);

        $status = $this->status($data['status'] ?? TaskStatus::WaitingAdmin);
        $assignedTo = isset($data['assigned_to']) ? (int) $data['assigned_to'] : null;
        $assignedTo = $this->normalizeWaitingAdminAssignee($status, $assignedTo);

        return DB::transaction(fn (): Task => Task::query()->create([
            'project_id' => $project->id,
            'created_by' => $actor->id,
            'assigned_to' => $assignedTo,
            'title' => trim((string) $data['title']),
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'status' => $status,
            'priority' => $this->priority($data['priority'] ?? TaskPriority::Normal),
            'due_date' => $data['due_date'] ?? null,
            'completed_at' => $status === TaskStatus::Completed ? now() : null,
        ]));
    }

    public function updateByAdmin(User $actor, Task $task, array $data): Task
    {
        $this->assertAdmin($actor);
        $task->loadMissing('project');
        $this->assertProjectOpen($task->project);

        return DB::transaction(function () use ($task, $data): Task {
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

            return $task->refresh();
        });
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

        return DB::transaction(function () use ($task, $status): Task {
            $task->status = $status;

            if ($status === TaskStatus::WaitingAdmin) {
                $task->assigned_to = null;
            }

            if ($status === TaskStatus::Completed) {
                $task->completed_at = now();
            }

            $task->save();

            return $task->refresh();
        });
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
