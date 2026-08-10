<?php

namespace Modules\Contacts\Infrastructure;

use App\Models\Contact;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Contacts\Domain\Contracts\ContactRepository;

class EloquentContactRepository implements ContactRepository
{
    public function search(?string $term = null): array
    {
        $term = trim((string) $term);

        return Contact::query()
            ->with('user.roles:id,name')
            ->when($term !== '', fn ($query) => $query->where(function ($nested) use ($term): void {
                foreach (preg_split('/\s+/u', $this->normalizeSearch($term)) ?: [] as $part) {
                    $like = '%'.addcslashes($part, '\\%_').'%';
                    $nested->where(fn ($search) => $search
                        ->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile', 'like', $like));
                }
            }))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Contact $contact) => $this->map($contact))
            ->all();
    }

    public function find(int $id): array
    {
        return $this->map(Contact::query()->with('user.roles:id,name')->findOrFail($id));
    }

    public function save(?int $id, array $contactAttributes, array $account): int
    {
        return DB::transaction(function () use ($id, $contactAttributes, $account): int {
            $contact = $id
                ? Contact::query()->with('user.roles:id,name')->findOrFail($id)
                : new Contact;

            $contact->fill($contactAttributes);
            $contact->save();

            $user = $contact->user;
            $enabled = (bool) ($account['enabled'] ?? false);

            if (! $enabled && ! $user) {
                return $contact->id;
            }

            if (! $user) {
                $role = (string) ($account['role'] ?? '');
                $this->assertAssignableRole($role, null);

                $user = User::create([
                    'contact_id' => $contact->id,
                    'password' => (string) $account['password'],
                    'is_active' => true,
                ]);
                $user->syncRoles([$role]);

                return $contact->id;
            }

            $attributes = ['is_active' => $enabled];
            $password = (string) ($account['password'] ?? '');
            if ($password !== '') {
                $attributes['password'] = $password;
            }
            $user->update($attributes);

            if ($enabled && isset($account['role']) && $account['role'] !== '') {
                $role = (string) $account['role'];
                $this->assertAssignableRole($role, $user);
                $user->syncRoles([$role]);
            }

            return $contact->id;
        });
    }

    private function assertAssignableRole(string $role, ?User $currentUser): void
    {
        if ($role === '') {
            throw new DomainException('account_role_required');
        }

        if ($role === 'admin' && ! $currentUser?->hasRole('admin')) {
            throw new DomainException('system_role_immutable');
        }
    }

    /** @return array<string,mixed> */
    private function map(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'full_name' => $contact->full_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'mobile' => $contact->mobile,
            'province' => $contact->province,
            'city' => $contact->city,
            'address' => $contact->address,
            'postal_code' => $contact->postal_code,
            'user_id' => $contact->user?->id,
            'account_enabled' => (bool) ($contact->user?->is_active ?? false),
            'role' => $contact->user?->roles->first()?->name,
            'created_at' => $contact->created_at,
        ];
    }

    private function normalizeSearch(string $search): string
    {
        return trim(strtr($search, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]));
    }
}
