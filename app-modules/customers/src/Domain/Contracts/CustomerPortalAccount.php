<?php

namespace Modules\Customers\Domain\Contracts;

interface CustomerPortalAccount
{
    public function create(string $name, string $email, string $password): int;

    public function update(int $userId, string $name, string $email, ?string $password = null): void;

    public function deactivate(int $userId): void;
}
