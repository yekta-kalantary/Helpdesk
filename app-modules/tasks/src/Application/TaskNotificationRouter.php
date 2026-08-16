<?php

namespace Modules\Tasks\Application;

use Illuminate\Support\Collection;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskNotificationRouter
{
    /** @return Collection<int, User> */
    public function created(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, User> */
    public function assigneeChanged(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, User> */
    public function statusChanged(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, User> */
    public function commentAdded(Task $task): Collection
    {
        return $this->taskAudience($task);
    }

    /** @return Collection<int, User> */
    public function activeAdmins(): Collection
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
}
