<?php

namespace Modules\Tasks\Application;

use App\Notifications\ResourceChangedNotification;
use App\Support\ActivityRecorder;
use App\Support\NotificationDispatcher;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskAssignmentRevoker
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /** @return Collection<int, Task> */
    public function requeueOpenCustomerAssignments(User $customer, User $actor, ?Project $project = null): Collection
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw new DomainException('Only an active admin may revoke customer assignments.');
        }

        if (! $customer->isCustomer()) {
            throw new DomainException('Only customer assignments can be revoked with this operation.');
        }

        $tasks = DB::transaction(function () use ($customer, $actor, $project): Collection {
            $tasks = Task::query()
                ->where('assigned_to', $customer->id)
                ->when($project, fn ($query) => $query->where('project_id', $project->id))
                ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
                ->lockForUpdate()
                ->get();

            foreach ($tasks as $task) {
                $oldStatus = $task->status;
                $oldAssignee = $task->assigned_to;

                $task->forceFill([
                    'status' => TaskStatus::WaitingAdmin,
                    'assigned_to' => null,
                ])->save();

                $this->activities->record($actor, 'task.assignee_changed', $task->project, $task, [
                    'old' => $oldAssignee,
                    'new' => null,
                ]);

                if ($oldStatus !== TaskStatus::WaitingAdmin) {
                    $this->activities->record($actor, 'task.status_changed', $task->project, $task, [
                        'old' => $oldStatus->value,
                        'new' => TaskStatus::WaitingAdmin->value,
                    ]);
                }
            }

            return $tasks;
        });

        foreach ($tasks as $task) {
            $this->notifications->send(
                User::query()->active()->admins()->get(),
                new ResourceChangedNotification(
                    'اقدام ادمین لازم است',
                    "تسک {$task->reference} پس از لغو مسئولیت مشتری به صف ادمین برگشت.",
                    url('/tasks/'.$task->id),
                    [
                        'resource_type' => 'task',
                        'resource_id' => $task->id,
                        'reference' => $task->reference,
                    ],
                ),
                $actor,
            );
        }

        return $tasks;
    }
}
