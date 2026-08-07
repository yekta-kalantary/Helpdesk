<?php

namespace Modules\Customers\Domain\Contracts;

interface CustomerPortalAccount
{
    /** @return array{name:string,last_name:string,email:string,mobile:string} */
    public function find(int $userId): array;

    public function create(string $name, string $lastName, string $email, string $mobile, string $password): int;

    public function update(
        int $userId,
        string $name,
        string $lastName,
        string $email,
        string $mobile,
        ?string $password = null,
    ): void;

    public function deactivate(int $userId): void;
}
