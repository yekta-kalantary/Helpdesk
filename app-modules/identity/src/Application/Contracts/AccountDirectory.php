<?php

namespace Modules\Identity\Application\Contracts;

use Modules\Identity\Application\DTOs\AccountSummary;

interface AccountDirectory
{
    public function find(int $accountId): ?AccountSummary;

    /** @return array<int> identifiers of every active administrator account */
    public function activeAdministratorIds(): array;
}
