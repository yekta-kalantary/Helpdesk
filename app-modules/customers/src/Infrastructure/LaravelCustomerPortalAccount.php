<?php

namespace Modules\Customers\Infrastructure;

use App\Models\User;
use Modules\Customers\Domain\Contracts\CustomerPortalAccount;

class LaravelCustomerPortalAccount implements CustomerPortalAccount
{
    public function find(int $userId): array
    {
        $user = User::query()->findOrFail($userId);

        return [
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
        ];
    }

    public function create(string $name, string $lastName, string $email, string $mobile, string $password): int
    {
        $user = User::create([
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'mobile' => $mobile,
            'password' => $password,
            'is_active' => true,
        ]);
        $user->assignRole('customer');

        return $user->id;
    }

    public function update(
        int $userId,
        string $name,
        string $lastName,
        string $email,
        string $mobile,
        ?string $password = null,
    ): void {
        $user = User::findOrFail($userId);
        $data = [
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'mobile' => $mobile,
            'is_active' => true,
        ];

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
