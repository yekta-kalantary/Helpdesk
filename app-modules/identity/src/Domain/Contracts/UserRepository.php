<?php

namespace Modules\Identity\Domain\Contracts;

interface UserRepository
{
    /** @return array<int, array{id:int,name:string,email:string,is_active:bool,role:?string}> */
    public function search(?string $term = null): array;

    /** @return array{id:int,name:string,email:string,is_active:bool,role:?string} */
    public function find(int $id): array;

    public function create(string $name, string $email, string $password, bool $isActive, string $role): int;

    public function update(int $id, string $name, string $email, ?string $password, bool $isActive, string $role): void;

    public function delete(int $id): void;
}
