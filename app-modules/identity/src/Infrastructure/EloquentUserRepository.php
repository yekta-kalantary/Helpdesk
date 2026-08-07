<?php

namespace Modules\Identity\Infrastructure;

use App\Enums\PersonType;
use App\Models\Person;
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
            ->select('users.*')
            ->join('people', 'people.id', '=', 'users.person_id')
            ->with(['person', 'roles:id,name'])
            ->where('people.type', PersonType::Employee->value)
            ->when($term, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('people.first_name', 'like', "%{$term}%")
                ->orWhere('people.last_name', 'like', "%{$term}%")
                ->orWhere('people.email', 'like', "%{$term}%")
                ->orWhere('people.mobile', 'like', "%{$term}%")))
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', self::SYSTEM_ROLES))
            ->orderBy('people.first_name')
            ->orderBy('people.last_name')
            ->get()
            ->map(fn (User $user) => $this->map($user))
            ->all();
    }

    public function find(int $id): array
    {
        $user = User::query()->with(['person', 'roles:id,name'])->findOrFail($id);
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

            $person = Person::create([
                'type' => PersonType::Employee,
                'first_name' => $name,
                'last_name' => $lastName,
                'email' => $email,
                'mobile' => $mobile,
            ]);

            $user = User::create([
                'person_id' => $person->id,
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
            $user = User::query()->with('person')->findOrFail($id);
            $this->assertTeamUser($user);
            $this->assertTeamRole($role);

            $user->person->update([
                'first_name' => $name,
                'last_name' => $lastName,
                'email' => $email,
                'mobile' => $mobile,
            ]);

            $attributes = ['is_active' => $isActive];

            if ($password !== null && $password !== '') {
                $attributes['password'] = $password;
            }

            $user->update($attributes);
            $user->syncRoles([$role]);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $user = User::query()->with('person')->findOrFail($id);
            $this->assertTeamUser($user);
            $person = $user->person;

            $user->delete();
            $person->delete();
        });
    }

    private function assertTeamRole(string $role): void
    {
        if (in_array($role, self::SYSTEM_ROLES, true)) {
            throw new DomainException('system_role_immutable');
        }
    }

    private function assertTeamUser(User $user): void
    {
        abort_if(
            $user->person?->type !== PersonType::Employee || $user->hasAnyRole(self::SYSTEM_ROLES),
            404,
        );
    }

    /** @return array{id:int,person_id:int,name:string,last_name:string,full_name:string,email:string,mobile:string,is_active:bool,role:?string} */
    private function map(User $user): array
    {
        return [
            'id' => $user->id,
            'person_id' => $user->person_id,
            'name' => $user->person->first_name,
            'last_name' => $user->person->last_name,
            'full_name' => $user->person->full_name,
            'email' => $user->person->email,
            'mobile' => $user->person->mobile,
            'is_active' => (bool) $user->is_active,
            'role' => $user->roles->first()?->name,
        ];
    }
}
