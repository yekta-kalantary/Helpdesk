<?php

namespace App\Policies;

use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Application\TaskAccess;
use Modules\Tasks\Infrastructure\Models\Attachment;

class AttachmentPolicy
{
    public function __construct(
        private AccountAuthenticationEligibility $eligibility,
        private TaskAccess $access,
    ) {}

    public function view(User $user, Attachment $attachment): bool
    {
        if (! $this->eligibility->canAuthenticateAccount($user->id)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($attachment->hidden_at !== null) {
            return false;
        }

        if ($attachment->comment_id !== null && ! $attachment->comment()->whereNull('hidden_at')->exists()) {
            return false;
        }

        return $this->access->canAccessTaskId($user->id, (int) $attachment->task_id);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return false;
    }
}
