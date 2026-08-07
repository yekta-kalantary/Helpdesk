<?php

namespace Modules\Customers\Infrastructure;

use App\Enums\PersonType;
use App\Models\Person;
use App\Models\User;
use DomainException;
use Modules\Customers\Domain\Contracts\CustomerPortalAccount;

class LaravelCustomerPortalAccount implements CustomerPortalAccount
{
    public function enable(int $personId, ?string $password = null): int
    {
        $person = Person::query()->findOrFail($personId);

        if ($person->type !== PersonType::Customer) {
            throw new DomainException('portal_account_requires_customer_person');
        }

        $user = User::query()->where('person_id', $personId)->first();

        if (! $user) {
            if ($password === null || $password === '') {
                throw new DomainException('portal_password_required');
            }

            $user = User::create([
                'person_id' => $personId,
                'password' => $password,
                'is_active' => true,
            ]);
        } else {
            $attributes = ['is_active' => true];

            if ($password !== null && $password !== '') {
                $attributes['password'] = $password;
            }

            $user->update($attributes);
        }

        $user->syncRoles(['customer']);

        return $user->id;
    }

    public function disable(int $personId): void
    {
        User::query()
            ->where('person_id', $personId)
            ->update(['is_active' => false]);
    }
}
