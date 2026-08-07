<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Modules\Identity\Domain\Access\PermissionCatalog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('keeps admin and customer as protected system roles', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    $adminRole = Role::findByName('admin', 'web');
    $customerRole = Role::findByName('customer', 'web');

    $this->actingAs($admin)->get(route('roles.edit', $adminRole->id))->assertNotFound();
    $this->actingAs($admin)->get(route('roles.edit', $customerRole->id))->assertNotFound();

    $this->actingAs($admin)
        ->put(route('roles.update', $adminRole->id), [
            'name' => 'renamed-admin',
            'permissions' => ['projects.view'],
        ])
        ->assertNotFound();

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $adminRole->id))
        ->assertSessionHasErrors('role');

    expect(Role::findByName('admin', 'web'))->not->toBeNull()
        ->and(Role::findByName('customer', 'web'))->not->toBeNull();
});

it('uses the code permission catalog as the source of truth', function (): void {
    expect(Route::has('permissions.store'))->toBeFalse()
        ->and(Route::has('permissions.destroy'))->toBeFalse();

    $databasePermissions = Permission::query()
        ->where('guard_name', 'web')
        ->pluck('name')
        ->sort()
        ->values()
        ->all();

    $catalogPermissions = collect(PermissionCatalog::all())
        ->sort()
        ->values()
        ->all();

    expect($databasePermissions)->toBe($catalogPermissions)
        ->and(array_keys(PermissionCatalog::groups()))->toBe([
            'customers',
            'projects',
            'tasks',
            'tickets',
            'identity',
            'reports',
            'settings',
        ]);
});

it('allows admin to create a dynamic role with catalog permissions only', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('roles.store'), [
            'name' => 'seo-manager',
            'permissions' => ['projects.view', 'tasks.view', 'reports.view'],
        ])
        ->assertRedirect(route('roles.index'));

    $role = Role::findByName('seo-manager', 'web');

    expect($role->hasPermissionTo('projects.view'))->toBeTrue()
        ->and($role->hasPermissionTo('tasks.view'))->toBeTrue()
        ->and($role->hasPermissionTo('reports.view'))->toBeTrue();

    Permission::findOrCreate('reports.export', 'web');

    $this->actingAs($admin)
        ->post(route('roles.store'), [
            'name' => 'invalid-role',
            'permissions' => ['reports.export'],
        ])
        ->assertSessionHasErrors('permissions.0');

    expect(Role::query()->where('name', 'invalid-role')->exists())->toBeFalse();
});

it('assigns exactly one non-system role to each staff member', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    Role::findOrCreate('seo-manager', 'web');
    Role::findOrCreate('developer', 'web');

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Staff Member',
            'email' => 'staff@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'role' => 'seo-manager',
            'roles' => ['seo-manager', 'developer'],
        ])
        ->assertRedirect(route('users.index'));

    $staff = User::query()->where('email', 'staff@example.test')->firstOrFail();

    expect($staff->roles()->count())->toBe(1)
        ->and($staff->hasRole('seo-manager'))->toBeTrue()
        ->and($staff->hasRole('developer'))->toBeFalse();

    $this->actingAs($admin)
        ->put(route('users.update', $staff->id), [
            'name' => $staff->name,
            'email' => $staff->email,
            'is_active' => true,
            'role' => 'admin',
        ])
        ->assertSessionHasErrors('role');

    expect($staff->fresh()->hasRole('seo-manager'))->toBeTrue();
});
