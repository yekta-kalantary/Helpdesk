<?php

namespace Modules\Tasks\Application;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Audit\Application\ActivityRecorder;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Notifications\Application\Contracts\ResourceChangedNotificationFactory;
use Modules\Notifications\Application\NotificationDispatcher;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Tasks\Infrastructure\Models\Task;

class CustomerAssignmentRequeuer
{
    public function __construct(
        private readonly AccountDirectory $accounts,
        private readonly ProjectMembershipDirectory $projects,
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
        private readonly ResourceChangedNotificationFactory $notificationFactory,
    ) {}

    /** @return Collection<int, Task> */
    public function requeue(int $userId, int $actorId, ?int $projectId = null): Collection
    {
        $this->assertValidActors($userId, $actorId);

        $tasks = DB::transaction(function () use ($userId, $actorId, $projectId): Collection {
            $query = Task::query()
                ->where('assigned_to', $userId)
                ->whereNull('completed_at')
                ->when($projectId, fn (Builder $tasks): Builder => $tasks->where('project_id', $projectId));

            foreach ((clone $query)->distinct()->orderBy('project_id')->pluck('project_id') as $lockedProjectId) {
                $this->projects->findProjectForUpdate((int) $lockedProjectId);
            }

            $tasks = $query->lockForUpdate()->get();
            foreach ($tasks as $task) {
                $oldAssignee = $task->assigned_to;
                $task->forceFill(['assigned_to' => null])->save();

                $this->activities->recordIds($actorId, 'task.assignee_changed', (int) $task->project_id, $task->id, [
                    'old' => $oldAssignee !== null ? (int) $oldAssignee : null,
                    'new' => null,
                    'reason' => 'customer_membership_or_account_change',
                ]);
            }

            return $tasks;
        });

        foreach ($tasks as $task) {
            $this->notifications->sendToAccountIds(
                $this->accounts->activeAdministratorIds(),
                $this->notificationFactory->make(
                    'مسئول تسک نیاز به بازبینی دارد',
                    "مسئول تسک {$task->reference} به‌دلیل تغییر دسترسی مشتری خالی شد.",
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

        return $tasks;
    }

    private function assertValidActors(int $userId, int $actorId): void
    {
        $actor = $this->accounts->find($actorId);
        $user = $this->accounts->find($userId);

        if ($actor === null || ! $actor->isActive || $actor->role !== UserRole::Admin) {
            throw new DomainException('Only an active Admin may release Customer or Employee assignments.');
        }

        if ($user?->role === UserRole::Customer || $user?->role === UserRole::Employee) {
            return;
        }

        throw new DomainException('Only Customer or Employee assignments may be released.');
    }
}
