<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Modules\Identity\Domain\Access\PermissionCatalog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('keeps admin and customer as protected system roles', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    $adminRole = Role::findByName('admin', 'web');
    $customerRole = Role::findByName('customer', 'web');

    $this->actingAs($admin)->get(route('roles.edit', $adminRole->id))->assertNotFound();
    $this->actingAs($admin)->get(route('roles.edit', $customerRole->id))->assertNotFound();

    Livewire::actingAs($admin)
        ->test('identity::roles.index')
        ->call('delete', $adminRole->id)
        ->assertHasErrors('role');

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

    Livewire::actingAs($admin)
        ->test('identity::roles.form')
        ->set('name', 'seo-manager')
        ->set('permissions', ['projects.view', 'tasks.view', 'reports.view'])
        ->call('save')
        ->assertRedirectToRoute('roles.index');

    $role = Role::findByName('seo-manager', 'web');

    expect($role->hasPermissionTo('projects.view'))->toBeTrue()
        ->and($role->hasPermissionTo('tasks.view'))->toBeTrue()
        ->and($role->hasPermissionTo('reports.view'))->toBeTrue();

    Permission::findOrCreate('reports.export', 'web');

    Livewire::actingAs($admin)
        ->test('identity::roles.form')
        ->set('name', 'invalid-role')
        ->set('permissions', ['reports.export'])
        ->call('save')
        ->assertHasErrors('permissions.0');

    expect(Role::query()->where('name', 'invalid-role')->exists())->toBeFalse();
});

it('assigns exactly one non-system role to each staff member', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    Role::findOrCreate('seo-manager', 'web');
    Role::findOrCreate('developer', 'web');

    Livewire::actingAs($admin)
        ->test('identity::users.form')
        ->set('name', 'Staff')
        ->set('last_name', 'Member')
        ->set('email', 'staff@example.test')
        ->set('mobile', '09120000001')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('is_active', true)
        ->set('role', 'seo-manager')
        ->call('save')
        ->assertRedirectToRoute('users.index');

    $staff = User::query()->where('email', 'staff@example.test')->firstOrFail();

    expect($staff->roles()->count())->toBe(1)
        ->and($staff->hasRole('seo-manager'))->toBeTrue()
        ->and($staff->hasRole('developer'))->toBeFalse()
        ->and($staff->full_name)->toBe('Staff Member')
        ->and($staff->mobile)->toBe('09120000001');

    Livewire::actingAs($admin)
        ->test('identity::users.form', ['user' => $staff->id])
        ->set('role', 'admin')
        ->call('save')
        ->assertHasErrors('role');

    expect($staff->fresh()->hasRole('seo-manager'))->toBeTrue();
});
