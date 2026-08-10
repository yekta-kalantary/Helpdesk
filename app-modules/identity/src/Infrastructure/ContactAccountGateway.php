<?php

namespace Modules\Identity\Infrastructure;

use DomainException;
use Modules\Contacts\Domain\Contracts\ContactAccountGateway as ContactAccountGatewayContract;
use Modules\Identity\Infrastructure\Models\User;
use Spatie\Permission\Models\Role;

class ContactAccountGateway implements ContactAccountGatewayContract
{
    private const SYSTEM_ROLES = ['admin'];

    public function get(int $contactId): array
    {
        $user = User::query()
            ->with('roles:id,name')
            ->where('contact_id', $contactId)
            ->first();

        return [
            'user_id' => $user?->id,
            'account_enabled' => (bool) ($user?->is_active ?? false),
            'role' => $user?->roles->first()?->name,
        ];
    }

    public function enabledFor(array $contactIds): array
    {
        if ($contactIds === []) {
            return [];
        }

        return User::query()
            ->whereIn('contact_id', $contactIds)
            ->pluck('is_active', 'contact_id')
            ->map(fn ($enabled): bool => (bool) $enabled)
            ->all();
    }

    public function assignableRoles(?int $contactId): array
    {
        $user = $contactId
            ? User::query()->with('roles:id,name')->where('contact_id', $contactId)->first()
            : null;

        if ($user?->hasRole('admin')) {
            return ['admin'];
        }

        return Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', self::SYSTEM_ROLES)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function save(int $contactId, array $account): void
    {
        $user = User::query()
            ->with('roles:id,name')
            ->where('contact_id', $contactId)
            ->first();

        $enabled = (bool) ($account['enabled'] ?? false);
        $role = trim((string) ($account['role'] ?? ''));
        $password = (string) ($account['password'] ?? '');

        if (! $user) {
            if (! $enabled) {
                return;
            }

            $this->assertAssignableRole($role);

            $user = User::create([
                'contact_id' => $contactId,
                'password' => $password,
                'is_active' => true,
            ]);
            $user->syncRoles([$role]);

            return;
        }

        if ($user->hasRole('admin')) {
            if (! $enabled || $role !== 'admin') {
                throw new DomainException('system_role_immutable');
            }
        } elseif ($enabled) {
            $this->assertAssignableRole($role);
        }

        $attributes = ['is_active' => $enabled];
        if ($password !== '') {
            $attributes['password'] = $password;
        }

        $user->update($attributes);

        if ($enabled && $role !== '') {
            $user->syncRoles([$role]);
        }
    }

    private function assertAssignableRole(string $role): void
    {
        if ($role === '' || in_array($role, self::SYSTEM_ROLES, true)) {
            throw new DomainException($role === '' ? 'account_role_required' : 'system_role_immutable');
        }

        if (! Role::query()->where('guard_name', 'web')->where('name', $role)->exists()) {
            throw new DomainException('account_role_required');
        }
    }
}
