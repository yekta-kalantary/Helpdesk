<?php

namespace Modules\Identity\Infrastructure;

use Modules\Identity\Domain\Contracts\UserRepository;
use Modules\Identity\Infrastructure\Models\User;

class EloquentUserRepository implements UserRepository
{
    public function search(?string $term = null): array
    {
        return User::query()
            ->where('is_admin', false)
            ->when($term, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")))
            ->orderBy('name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $user) => $this->map($user))
            ->all();
    }

    public function find(int $id): array
    {
        $user = User::query()
            ->where('is_admin', false)
            ->findOrFail($id);

        return $this->map($user);
    }

    public function create(
        string $name,
        string $lastName,
        string $email,
        ?string $mobile,
        string $password,
        bool $isActive,
    ): int {
        return User::query()->create([
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'mobile' => $mobile,
            'password' => $password,
            'is_active' => $isActive,
            'is_admin' => false,
        ])->id;
    }

    public function update(
        int $id,
        string $name,
        string $lastName,
        string $email,
        ?string $mobile,
        ?string $password,
        bool $isActive,
    ): void {
        $user = User::query()
            ->where('is_admin', false)
            ->findOrFail($id);

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
    }

    /** @return array{id:int,name:string,last_name:string,full_name:string,email:string,mobile:?string,is_active:bool} */
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
        ];
    }
}
