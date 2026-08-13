<?php

namespace App\Support;

use App\Notifications\ResourceChangedNotification;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Application\TaskNotificationRouter;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

class CustomerAssignmentRequeuer
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
        private readonly TaskNotificationRouter $notificationRouter,
    ) {}

    /** @return Collection<int, Task> */
    public function requeue(User $customer, User $actor, ?Project $project = null): Collection
    {
        $this->assertValidActors($customer, $actor);

        $tasks = DB::transaction(function () use ($customer, $actor, $project): Collection {
            $projectIds = $project
                ? [$project->id]
                : Task::query()
                    ->where('assigned_to', $customer->id)
                    ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
                    ->distinct()
                    ->orderBy('project_id')
                    ->pluck('project_id')
                    ->all();

            foreach ($projectIds as $projectId) {
                Project::query()->whereKey($projectId)->lockForUpdate()->firstOrFail();
            }

            $tasks = Task::query()
                ->where('assigned_to', $customer->id)
                ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
                ->when($project, fn (Builder $query) => $query->where('project_id', $project->id))
                ->with('project')
                ->lockForUpdate()
                ->get();

            foreach ($tasks as $task) {
                $oldStatus = $task->status;
                $oldAssignee = $task->assigned_to;

                $task->forceFill([
                    'status' => TaskStatus::WaitingAdmin,
                    'assigned_to' => null,
                    'completed_at' => null,
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
                $this->notificationRouter->adminQueue(),
                new ResourceChangedNotification(
                    'اقدام ادمین لازم است',
                    "تسک {$task->reference} به صف ادمین برگشت.",
                    route('tasks.show', $task),
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

    private function assertValidActors(User $customer, User $actor): void
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw new DomainException('Only an active admin may requeue customer assignments.');
        }

        if (! $customer->isCustomer()) {
            throw new DomainException('Only customer assignments may be requeued.');
        }
    }
}
