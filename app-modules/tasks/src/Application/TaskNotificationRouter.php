<?php

namespace Modules\Tasks\Application;

use Illuminate\Support\Collection;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskNotificationRouter
{
    /** @return Collection<int, int> */
    public function created(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, int> */
    public function assigneeChanged(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, int> */
    public function statusChanged(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, int> */
    public function commentAdded(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, int> */
    private function taskAudience(Task $task): Collection
    {
        return collect([$task->created_by, $task->assigned_to])
            ->filter()
            ->map(static fn (mixed $accountId): int => (int) $accountId)
            ->unique()
            ->values();
    }
}
