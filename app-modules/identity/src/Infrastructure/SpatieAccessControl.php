<?php

namespace Modules\Identity\Infrastructure;

use DomainException;
use Modules\Identity\Domain\Access\PermissionCatalog;
use Modules\Identity\Domain\Contracts\AccessControl;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SpatieAccessControl implements AccessControl
{
    private const SYSTEM_ROLES = ['admin'];

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
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', PermissionCatalog::all())
            ->get(['id', 'name'])
            ->keyBy('name');

        $result = [];

        foreach (PermissionCatalog::groups() as $module => $names) {
            foreach ($names as $name) {
                $permission = $permissions->get($name);

                if (! $permission) {
                    continue;
                }

                $result[] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $module,
                ];
            }
        }

        return $result;
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
}
