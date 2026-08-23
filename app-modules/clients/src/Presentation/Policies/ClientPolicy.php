<?php

namespace Modules\Clients\Presentation\Policies;

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Domain\Enums\UserRole;

class ClientPolicy
{
    public function __construct(
        private readonly AccountDirectory $accounts,
    ) {}

    public function before(object $account): ?bool
    {
        return $this->isActiveAdmin($account) ? true : null;
    }

    public function viewAny(object $account): bool
    {
        return false;
    }

    public function view(object $account, Client $client): bool
    {
        return false;
    }

    public function create(object $account): bool
    {
        return false;
    }

    public function update(object $account, Client $client): bool
    {
        return false;
    }

    public function delete(object $account, Client $client): bool
    {
        return false;
    }

    private function isActiveAdmin(object $account): bool
    {
        $summary = $this->accounts->find((int) $account->id);

        return $summary !== null && $summary->isActive && $summary->role === UserRole::Admin;
    }
}
