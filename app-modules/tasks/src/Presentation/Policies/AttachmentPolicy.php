<?php

namespace Modules\Tasks\Presentation\Policies;

use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Tasks\Application\TaskAccess;
use Modules\Tasks\Infrastructure\Models\Attachment;

class AttachmentPolicy
{
    public function __construct(
        private readonly AccountAuthenticationEligibility $eligibility,
        private readonly AccountDirectory $accounts,
        private readonly TaskAccess $access,
    ) {}

    public function view(object $account, Attachment $attachment): bool
    {
        if (! $this->eligibility->canAuthenticateAccount((int) $account->id)) {
            return false;
        }

        if ($this->isActiveAdmin($account)) {
            return true;
        }

        if ($attachment->hidden_at !== null) {
            return false;
        }

        if ($attachment->comment_id !== null && ! $attachment->comment()->whereNull('hidden_at')->exists()) {
            return false;
        }

        return $this->access->canAccessTaskId((int) $account->id, (int) $attachment->task_id);
    }

    public function delete(object $account, Attachment $attachment): bool
    {
        return false;
    }

    private function isActiveAdmin(object $account): bool
    {
        $summary = $this->accounts->find((int) $account->id);

        return $summary !== null && $summary->isActive && $summary->role === UserRole::Admin;
    }
}
