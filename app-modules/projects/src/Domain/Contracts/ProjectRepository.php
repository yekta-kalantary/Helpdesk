<?php

namespace Modules\Projects\Domain\Contracts;

interface ProjectRepository
{
    /** @return array<int, array<string,mixed>> */
    public function search(?string $term = null, ?int $userId = null, bool $isAdmin = false): array;

    /** @return array<string,mixed> */
    public function find(int $id): array;

    public function create(array $attributes, array $memberIds): int;

    public function update(int $id, array $attributes, array $memberIds): void;

    public function delete(int $id): void;
}
