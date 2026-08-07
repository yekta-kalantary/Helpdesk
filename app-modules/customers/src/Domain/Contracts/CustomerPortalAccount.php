<?php

namespace Modules\Customers\Domain\Contracts;

interface CustomerPortalAccount
{
    public function enable(int $personId, ?string $password = null): int;

    public function disable(int $personId): void;
}
