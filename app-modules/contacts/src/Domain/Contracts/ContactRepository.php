<?php

namespace Modules\Contacts\Domain\Contracts;

interface ContactRepository
{
    /** @return array<int,array<string,mixed>> */
    public function search(?string $term = null): array;

    /** @return array<string,mixed> */
    public function find(int $id): array;

    public function save(?int $id, array $contactAttributes): int;
}
