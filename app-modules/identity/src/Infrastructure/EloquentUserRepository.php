<?php

namespace Modules\Identity\Infrastructure;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Contacts\Domain\Contracts\ContactRepository;
use Modules\Identity\Domain\Contracts\UserRepository;
use Modules\Identity\Infrastructure\Models\User;

class EloquentUserRepository implements UserRepository
{
    private const SYSTEM_ROLES = ['admin'];

    public function __construct(private readonly ContactRepository $contacts) {}

    public function search(?string $term = null): array
    {
        return User::query()
            ->select('users.*')
            ->join('contacts', 'contacts.id', '=', 'users.contact_id')
            ->with(['contact', 'roles:id,name'])
            ->when($term, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('contacts.first_name', 'like', "%{$term}%")
                ->orWhere('contacts.last_name', 'like', "%{$term}%")
                ->orWhere('contacts.email', 'like', "%{$term}%")
                ->orWhere('contacts.mobile', 'like', "%{$term}%")))
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', self::SYSTEM_ROLES))
            ->orderBy('contacts.first_name')
            ->orderBy('contacts.last_name')
            ->get()
            ->map(fn (User $user) => $this->map($user))
            ->all();
    }

    public function find(int $id): array
    {
        $user = User::query()->with(['contact', 'roles:id,name'])->findOrFail($id);
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

            $contactId = $this->contacts->save(null, [
                'first_name' => $name,
                'last_name' => $lastName,
                'email' => $email,
                'mobile' => $mobile,
            ]);

            $user = User::create([
                'contact_id' => $contactId,
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
            $user = User::query()->with('contact')->findOrFail($id);
            $this->assertTeamUser($user);
            $this->assertTeamRole($role);

            $this->contacts->save($user->contact_id, [
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

    /** @return array{id:int,contact_id:int,name:string,last_name:string,full_name:string,email:string,mobile:string,is_active:bool,role:?string} */
    private function map(User $user): array
    {
        return [
            'id' => $user->id,
            'contact_id' => $user->contact_id,
            'name' => $user->contact->first_name,
            'last_name' => $user->contact->last_name,
            'full_name' => $user->contact->full_name,
            'email' => $user->contact->email,
            'mobile' => $user->contact->mobile,
            'is_active' => (bool) $user->is_active,
            'role' => $user->roles->first()?->name,
        ];
    }
}
