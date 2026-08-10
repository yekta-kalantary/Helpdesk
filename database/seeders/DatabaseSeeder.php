<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Contacts\Infrastructure\Models\Contact;
use Modules\Identity\Domain\Access\PermissionCatalog;
use Modules\Identity\Infrastructure\Models\User;
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

        Role::query()->where('name', 'customer')->where('guard_name', 'web')->delete();

        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->syncPermissions($permissions);

        $contact = Contact::query()->updateOrCreate(
            ['email' => config('helpdesk.admin.email')],
            [
                'first_name' => config('helpdesk.admin.first_name'),
                'last_name' => config('helpdesk.admin.last_name'),
                'mobile' => config('helpdesk.admin.mobile'),
            ],
        );

        $admin = User::query()->updateOrCreate(
            ['contact_id' => $contact->id],
            [
                'password' => config('helpdesk.admin.password'),
                'is_active' => true,
            ],
        );
        $admin->syncRoles(['admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
