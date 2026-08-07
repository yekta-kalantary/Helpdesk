<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'tasks.comment',
            'tasks.manage_all',
            'tickets.view',
            'tickets.create',
            'tickets.reply',
            'tickets.manage',
            'tickets.delete',
            'tickets.manage_all',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'settings.manage',
            'notifications.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::findOrCreate('admin', 'web');
        $customerRole = Role::findOrCreate('customer', 'web');

        $adminRole->syncPermissions($permissions);
        $customerRole->syncPermissions([
            'projects.view',
            'tasks.view',
            'tickets.view',
            'tickets.create',
            'tickets.reply',
            'notifications.view',
        ]);

        $admin = User::query()->updateOrCreate(
            ['email' => config('helpdesk.admin.email')],
            [
                'name' => config('helpdesk.admin.name'),
                'password' => config('helpdesk.admin.password'),
                'is_active' => true,
            ],
        );
        $admin->syncRoles(['admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
