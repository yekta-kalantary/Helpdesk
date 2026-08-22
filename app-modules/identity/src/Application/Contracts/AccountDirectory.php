<?php

namespace Modules\Identity\Application\Contracts;

use Modules\Identity\Application\DTOs\AccountSummary;

interface AccountDirectory
{
    public function find(int $accountId): ?AccountSummary;
}
