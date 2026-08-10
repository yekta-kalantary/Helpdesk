<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Infrastructure\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('helpdesk.admin.email')],
            [
                'name' => config('helpdesk.admin.first_name'),
                'last_name' => config('helpdesk.admin.last_name'),
                'mobile' => config('helpdesk.admin.mobile'),
                'password' => config('helpdesk.admin.password'),
                'is_active' => true,
                'is_admin' => true,
            ],
        );
    }
}
