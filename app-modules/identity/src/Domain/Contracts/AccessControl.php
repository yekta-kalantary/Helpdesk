<?php

namespace Modules\Identity\Domain\Contracts;

interface AccessControl
{
    /** @return array<int, array{id:int,name:string,permissions:array<int,string>,system:bool}> */
    public function roles(): array;

    /** @return array<int, array{id:int,name:string,module:string}> */
    public function permissions(): array;

    public function createRole(string $name, array $permissions): void;

    public function updateRole(int $roleId, string $name, array $permissions): void;

    public function deleteRole(int $roleId): void;
}
