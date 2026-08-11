<?php

namespace App\Policies;

use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;

class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        if (! $user->canAuthenticate()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($attachment->hidden_at !== null) {
            return false;
        }

        return Task::query()->visibleTo($user)->whereKey($attachment->task_id)->exists();
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return false;
    }
}
