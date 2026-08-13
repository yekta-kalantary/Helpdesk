<?php

namespace Modules\Tasks\Application;

use Illuminate\Support\Collection;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskNotificationRouter
{
    /** @return Collection<int, User> */
    public function created(Task $task): Collection
    {
        $task->loadMissing('assignee');

        if ($task->assignee) {
            return collect([$task->assignee]);
        }

        return $this->isUnassignedAdminQueue($task)
            ? $this->adminQueue()
            : collect();
    }

    /** @return Collection<int, User> */
    public function assigneeChanged(Task $task): Collection
    {
        $task->loadMissing('assignee');

        if ($task->assignee) {
            return collect([$task->assignee]);
        }

        return $this->isUnassignedAdminQueue($task)
            ? $this->adminQueue()
            : collect();
    }

    /** @return Collection<int, User> */
    public function statusChanged(Task $task): Collection
    {
        return $this->withAdminQueueFallback($task, $this->taskAudience($task));
    }

    /** @return Collection<int, User> */
    public function commentAdded(Task $task): Collection
    {
        return $this->withAdminQueueFallback($task, $this->taskAudience($task));
    }

    /** @return Collection<int, User> */
    public function adminQueue(): Collection
    {
        return User::query()->active()->admins()->get();
    }

    /** @return Collection<int, User> */
    private function taskAudience(Task $task): Collection
    {
        $task->loadMissing(['creator', 'assignee']);

        return collect([$task->creator, $task->assignee])
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * @param  Collection<int, User>  $recipients
     * @return Collection<int, User>
     */
    private function withAdminQueueFallback(Task $task, Collection $recipients): Collection
    {
        if ($this->isUnassignedAdminQueue($task)) {
            return $recipients
                ->merge($this->adminQueue())
                ->unique('id')
                ->values();
        }

        return $recipients;
    }

    private function isUnassignedAdminQueue(Task $task): bool
    {
        return $task->status === TaskStatus::WaitingAdmin && $task->assigned_to === null;
    }
}
