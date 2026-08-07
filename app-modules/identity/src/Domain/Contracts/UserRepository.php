<?php

namespace Modules\Identity\Domain\Contracts;

interface UserRepository
{
    /** @return array<int, array{id:int,person_id:int,name:string,last_name:string,full_name:string,email:string,mobile:string,is_active:bool,role:?string}> */
    public function search(?string $term = null): array;

    /** @return array{id:int,person_id:int,name:string,last_name:string,full_name:string,email:string,mobile:string,is_active:bool,role:?string} */
    public function find(int $id): array;

    public function create(
        string $name,
        string $lastName,
        string $email,
        string $mobile,
        string $password,
        bool $isActive,
        string $role,
    ): int;

    public function update(
        int $id,
        string $name,
        string $lastName,
        string $email,
        string $mobile,
        ?string $password,
        bool $isActive,
        string $role,
    ): void;
}
