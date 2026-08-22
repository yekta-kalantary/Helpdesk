<?php

namespace Modules\Identity\Infrastructure\Queries;

use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Application\DTOs\AccountSummary;
use Modules\Identity\Infrastructure\Models\User;

final class EloquentAccountDirectory implements AccountDirectory
{
    public function find(int $accountId): ?AccountSummary
    {
        $account = User::query()->find($accountId);

        return $account === null
            ? null
            : new AccountSummary($account->id, $account->role, $account->is_active, $account->client_id);
    }
}
