<?php

namespace Modules\Tasks\Infrastructure;

use App\Models\User;
use Modules\Tasks\Domain\Contracts\TaskNotifier;
use Modules\Tasks\Infrastructure\Notifications\TaskAssignedNotification;

class DatabaseTaskNotifier implements TaskNotifier
{
    public function assigned(int $userId, int $taskId, string $title): void
    {
        User::query()->find($userId)?->notify(new TaskAssignedNotification($taskId, $title));
    }
}
