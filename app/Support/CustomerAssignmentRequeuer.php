<?php

namespace App\Support;

use App\Notifications\ResourceChangedNotification;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Infrastructure\Models\Task;

class CustomerAssignmentRequeuer
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /** @return Collection<int, Task> */
    public function requeue(User $user, User $actor, ?Project $project = null): Collection
    {
        $this->assertValidActors($user, $actor);

        $tasks = DB::transaction(function () use ($user, $actor, $project): Collection {
            $query = Task::query()
                ->where('assigned_to', $user->id)
                ->whereNull('completed_at')
                ->when($project, fn (Builder $tasks): Builder => $tasks->where('project_id', $project->id));

            $projectIds = (clone $query)->distinct()->orderBy('project_id')->pluck('project_id')->all();
            foreach ($projectIds as $projectId) {
                Project::query()->whereKey($projectId)->lockForUpdate()->firstOrFail();
            }

            $tasks = $query->lockForUpdate()->get();
            foreach ($tasks as $task) {
                $oldAssignee = $task->assigned_to;
                $task->forceFill(['assigned_to' => null])->save();

                $this->activities->recordIds($actor->id, 'task.assignee_changed', (int) $task->project_id, $task->id, [
                    'old' => $oldAssignee !== null ? (int) $oldAssignee : null,
                    'new' => null,
                    'reason' => 'customer_membership_or_account_change',
                ]);
            }

            return $tasks;
        });

        foreach ($tasks as $task) {
            $this->notifications->sendToAccountIds(
                User::query()->active()->admins()->pluck('id'),
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
                $actor->id,
            );
        }

        return $tasks;
    }

    private function assertValidActors(User $user, User $actor): void
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw new DomainException('Only an active Admin may release Customer or Employee assignments.');
        }

        if ($user->isCustomer()) {
            return;
        }

        if ($user->isEmployee()) {
            return;
        }

        throw new DomainException('Only Customer or Employee assignments may be released.');
    }
}
