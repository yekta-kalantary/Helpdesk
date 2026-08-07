<?php

namespace Database\Seeders;

use App\Enums\PersonType;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Identity\Domain\Access\PermissionCatalog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = PermissionCatalog::all();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $permissions)
            ->delete();

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

        $person = Person::query()->updateOrCreate(
            ['email' => config('helpdesk.admin.email')],
            [
                'type' => PersonType::Employee,
                'first_name' => config('helpdesk.admin.first_name'),
                'last_name' => config('helpdesk.admin.last_name'),
                'mobile' => config('helpdesk.admin.mobile'),
            ],
        );

        $admin = User::query()->updateOrCreate(
            ['person_id' => $person->id],
            [
                'password' => config('helpdesk.admin.password'),
                'is_active' => true,
            ],
        );
        $admin->syncRoles(['admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
