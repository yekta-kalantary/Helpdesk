<?php

namespace Modules\Customers\Infrastructure;

use App\Models\User;
use Modules\Customers\Domain\Contracts\CustomerPortalAccount;

class LaravelCustomerPortalAccount implements CustomerPortalAccount
{
    public function create(string $name, string $email, string $password): int
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);
        $user->assignRole('customer');

        return $user->id;
    }

    public function update(int $userId, string $name, string $email, ?string $password = null): void
    {
        $user = User::findOrFail($userId);
        $data = ['name' => $name, 'email' => $email, 'is_active' => true];

        if ($password !== null && $password !== '') {
            $data['password'] = $password;
        }

        $user->update($data);
        $user->syncRoles(['customer']);
    }

    public function deactivate(int $userId): void
    {
        User::query()->whereKey($userId)->update(['is_active' => false]);
    }
}
