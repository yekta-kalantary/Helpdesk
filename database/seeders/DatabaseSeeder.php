<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Identity\Infrastructure\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('helpdesk.admin.email')],
            [
                'client_id' => null,
                'role' => UserRole::Admin,
                'name' => config('helpdesk.admin.first_name'),
                'last_name' => config('helpdesk.admin.last_name'),
                'mobile' => config('helpdesk.admin.mobile'),
                'password' => config('helpdesk.admin.password'),
                'is_active' => true,
            ],
        );
    }
}
