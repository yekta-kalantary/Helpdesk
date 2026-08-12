<?php

namespace Modules\Tasks\Application;

use Illuminate\Support\Collection;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Task;

class TaskNotificationRouter
{
    /** @return Collection<int, User> */
    public function statusChanged(Task $task): Collection
    {
        $task->loadMissing(['creator', 'assignee']);

        return collect([$task->creator, $task->assignee])
            ->filter()
            ->unique('id')
            ->values();
    }
}
