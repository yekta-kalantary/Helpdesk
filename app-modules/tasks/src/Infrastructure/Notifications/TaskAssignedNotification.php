<?php

namespace Modules\Tasks\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly int $taskId,
        private readonly string $taskTitle,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'task_assigned',
            'task_id' => $this->taskId,
            'task_title' => $this->taskTitle,
            'message_key' => 'tasks::messages.notification.assigned',
        ];
    }
}
