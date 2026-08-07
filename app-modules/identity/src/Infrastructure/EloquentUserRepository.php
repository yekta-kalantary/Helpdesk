<?php

namespace Modules\Identity\Infrastructure;

use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Domain\Contracts\UserRepository;

class EloquentUserRepository implements UserRepository
{
    private const SYSTEM_ROLES = ['admin', 'customer'];

    public function search(?string $term = null): array
    {
        return User::query()
            ->with('roles:id,name')
            ->when($term, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")))
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', self::SYSTEM_ROLES))
            ->latest('id')
            ->get()
            ->map(fn (User $user) => $this->map($user))
            ->all();
    }

    public function find(int $id): array
    {
        $user = User::query()->with('roles:id,name')->findOrFail($id);
        $this->assertTeamUser($user);

        return $this->map($user);
    }

    public function create(
        string $name,
        string $lastName,
        string $email,
        string $mobile,
        string $password,
        bool $isActive,
        string $role,
    ): int {
        return DB::transaction(function () use ($name, $lastName, $email, $mobile, $password, $isActive, $role): int {
            $this->assertTeamRole($role);

            $user = User::create([
                'name' => $name,
                'last_name' => $lastName,
                'email' => $email,
                'mobile' => $mobile,
                'password' => $password,
                'is_active' => $isActive,
            ]);

            $user->syncRoles([$role]);

            return $user->id;
        });
    }

    public function update(
        int $id,
        string $name,
        string $lastName,
        string $email,
        string $mobile,
        ?string $password,
        bool $isActive,
        string $role,
    ): void {
        DB::transaction(function () use ($id, $name, $lastName, $email, $mobile, $password, $isActive, $role): void {
            $user = User::findOrFail($id);
            $this->assertTeamUser($user);
            $this->assertTeamRole($role);

            $attributes = [
                'name' => $name,
                'last_name' => $lastName,
                'email' => $email,
                'mobile' => $mobile,
                'is_active' => $isActive,
            ];

            if ($password !== null && $password !== '') {
                $attributes['password'] = $password;
            }

            $user->update($attributes);
            $user->syncRoles([$role]);
        });
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);
        $this->assertTeamUser($user);
        $user->delete();
    }

    private function assertTeamRole(string $role): void
    {
        if (in_array($role, self::SYSTEM_ROLES, true)) {
            throw new DomainException('system_role_immutable');
        }
    }

    private function assertTeamUser(User $user): void
    {
        abort_if($user->hasAnyRole(self::SYSTEM_ROLES), 404);
    }

    /** @return array{id:int,name:string,last_name:string,full_name:string,email:string,mobile:string,is_active:bool,role:?string} */
    private function map(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'is_active' => (bool) $user->is_active,
            'role' => $user->roles->first()?->name,
        ];
    }
}
