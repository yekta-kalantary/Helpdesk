<?php

namespace Modules\Identity\Infrastructure;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Domain\Contracts\UserRepository;

class EloquentUserRepository implements UserRepository
{
    public function search(?string $term = null): array
    {
        return User::query()
            ->with('roles:id,name')
            ->when($term, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->latest('id')
            ->get()
            ->map(fn (User $user) => $this->map($user))
            ->all();
    }

    public function find(int $id): array
    {
        $user = User::query()->with('roles:id,name')->findOrFail($id);

        return $this->map($user);
    }

    public function create(string $name, string $email, string $password, bool $isActive, array $roles): int
    {
        return DB::transaction(function () use ($name, $email, $password, $isActive, $roles): int {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => $isActive,
            ]);

            $user->syncRoles($this->sanitizeTeamRoles($roles));

            return $user->id;
        });
    }

    public function update(int $id, string $name, string $email, ?string $password, bool $isActive, array $roles): void
    {
        DB::transaction(function () use ($id, $name, $email, $password, $isActive, $roles): void {
            $user = User::findOrFail($id);
            $attributes = ['name' => $name, 'email' => $email, 'is_active' => $isActive];

            if ($password !== null && $password !== '') {
                $attributes['password'] = $password;
            }

            $user->update($attributes);
            $user->syncRoles($this->sanitizeTeamRoles($roles));
        });
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->hasRole(['admin', 'customer'])) {
            abort(422, 'System users cannot be deleted here.');
        }

        $user->delete();
    }

    private function sanitizeTeamRoles(array $roles): array
    {
        return array_values(array_filter($roles, static fn (string $role) => $role !== 'customer'));
    }

    /** @return array{id:int,name:string,email:string,is_active:bool,roles:array<int,string>} */
    private function map(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => (bool) $user->is_active,
            'roles' => $user->roles->pluck('name')->all(),
        ];
    }
}
