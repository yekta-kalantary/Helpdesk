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
            $query = Task::query()
                ->where('assigned_to', $customer->id)
                ->whereHas('projectStatus', fn (Builder $statuses): Builder => $statuses->where('is_done', false))
                ->when($project, fn (Builder $tasks): Builder => $tasks->where('project_id', $project->id));

            $projectIds = (clone $query)->distinct()->orderBy('project_id')->pluck('project_id')->all();
            foreach ($projectIds as $projectId) {
                Project::query()->whereKey($projectId)->lockForUpdate()->firstOrFail();
            }

            $tasks = $query->with('project')->lockForUpdate()->get();
            foreach ($tasks as $task) {
                $oldAssignee = $task->assigned_to;
                $task->forceFill(['assigned_to' => null])->save();

                $this->activities->record($actor, 'task.assignee_changed', $task->project, $task, [
                    'old' => $oldAssignee,
                    'new' => null,
                    'reason' => 'customer_membership_or_account_change',
                ]);
            }

            return $tasks;
        });

        foreach ($tasks as $task) {
            $this->notifications->send(
                $this->notificationRouter->adminQueue(),
                new ResourceChangedNotification(
                    'مسئول تسک نیاز به بازبینی دارد',
                    "مسئول تسک {$task->reference} به‌دلیل تغییر دسترسی مشتری خالی شد.",
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
            throw new DomainException('Only an active Admin may release Customer assignments.');
        }

        if (! $customer->isCustomer()) {
            throw new DomainException('Only Customer assignments may be released.');
        }
    }
}
