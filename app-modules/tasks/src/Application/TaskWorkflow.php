<?php

namespace Modules\Tasks\Application;

use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
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
        $this->assertAdmin((int) $actor->id);

        return DB::transaction(function () use ($task, $data): Task {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertProjectOpen($this->project($task->project_id));
            $oldStatus = $this->status($task->project_status_id, $task->project_id);

            if ($oldStatus->isDone) {
                throw new DomainException('Done Tasks must be reopened through a status transition before editing.');
            }

            $attributes = Arr::only($data, ['title', 'description', 'due_date']);
            if (array_key_exists('priority', $data)) {
                $attributes['priority'] = $this->priority($data['priority']);
            }
            if (array_key_exists('assigned_to', $data)) {
                $attributes['assigned_to'] = filled($data['assigned_to']) ? (int) $data['assigned_to'] : null;
            }
            if (array_key_exists('work_group_id', $data)) {
                $attributes['work_group_id'] = $this->workGroup($data['work_group_id'], $task->project_id)?->id;
            }
            if (array_key_exists('project_status_id', $data)) {
                $newStatus = $this->status((int) $data['project_status_id'], $task->project_id);
                $attributes['project_status_id'] = $newStatus->id;
                $attributes['completed_at'] = $newStatus->isDone ? now() : null;
            }

            $this->assertAssignee($task->project_id, $attributes['assigned_to'] ?? $task->assigned_to);
            $task->fill($attributes)->save();

            return $task->refresh();
        });
    }

    public function changeStatus(object $actor, Task $task, object $status): Task
    {
        $actorId = (int) $actor->id;
        $this->assertProjectAccess($actorId, $task->project_id);

        return DB::transaction(function () use ($task, $status): Task {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertProjectOpen($this->project($task->project_id));
            $target = $this->status((int) $status->id, $task->project_id);
            $old = $this->status($task->project_status_id, $task->project_id);

            if ($old->id !== $target->id) {
                $task->update(['project_status_id' => $target->id, 'completed_at' => $target->isDone ? now() : null]);
            }

            return $task->refresh();
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
        return DB::transaction(function () use ($actorId, $projectId, $data): Task {
            $this->assertProjectOpen($this->project($projectId));
            $status = $data['project_status_id'] ? $this->status((int) $data['project_status_id'], $projectId) : $this->defaultStatus($projectId);
            $workGroup = $this->workGroup($data['work_group_id'] ?? null, $projectId);
            $title = trim((string) ($data['title'] ?? ''));
            if ($title === '') {
                throw new DomainException('Task title is required.');
            }
            $assigneeId = filled($data['assigned_to'] ?? null) ? (int) $data['assigned_to'] : null;
            $this->assertAssignee($projectId, $assigneeId);

            return Task::query()->create([
                'project_id' => $projectId, 'project_status_id' => $status->id, 'work_group_id' => $workGroup?->id,
                'created_by' => $actorId, 'assigned_to' => $assigneeId, 'title' => $title,
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'priority' => $this->priority($data['priority'] ?? TaskPriority::Normal), 'due_date' => $data['due_date'] ?? null,
                'completed_at' => $status->isDone ? now() : null,
            ]);
        });
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
