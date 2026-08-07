<?php

namespace Modules\Identity\Infrastructure;

use DomainException;
use Modules\Identity\Domain\Contracts\AccessControl;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SpatieAccessControl implements AccessControl
{
    private const SYSTEM_ROLES = ['admin', 'customer'];

    public function roles(): array
    {
        return Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->all(),
                'system' => in_array($role->name, self::SYSTEM_ROLES, true),
            ])->all();
    }

    public function permissions(): array
    {
        return Permission::query()->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function createRole(string $name, array $permissions): void
    {
        if (in_array($name, self::SYSTEM_ROLES, true)) {
            throw new DomainException('system_role_immutable');
        }

        $role = Role::create(['name' => $name, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
    }

    public function updateRole(int $roleId, string $name, array $permissions): void
    {
        $role = Role::findOrFail($roleId);

        if (in_array($role->name, self::SYSTEM_ROLES, true)) {
            throw new DomainException('system_role_immutable');
        }

        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if (in_array($role->name, self::SYSTEM_ROLES, true)) {
            throw new DomainException('system_role_immutable');
        }

        $role->delete();
    }

    public function createPermission(string $name): void
    {
        Permission::create(['name' => $name, 'guard_name' => 'web']);
    }

    public function deletePermission(int $permissionId): void
    {
        Permission::findOrFail($permissionId)->delete();
    }
}
