<?php

namespace Modules\Customers\Domain\Contracts;

interface CustomerRepository
{
    /** @return array<int, array<string,mixed>> */
    public function search(?string $term = null): array;

    /** @return array<string,mixed> */
    public function find(int $id): array;

    public function create(array $personAttributes, array $customerAttributes): int;

    public function update(int $id, array $personAttributes, array $customerAttributes): void;
}
