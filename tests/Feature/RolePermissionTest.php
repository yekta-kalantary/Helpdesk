<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('keeps admin and customer as protected system roles', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    $adminRole = Role::findByName('admin', 'web');
    $customerRole = Role::findByName('customer', 'web');

    $this->actingAs($admin)->get(route('roles.edit', $adminRole->id))->assertNotFound();
    $this->actingAs($admin)->get(route('roles.edit', $customerRole->id))->assertNotFound();

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $adminRole->id))
        ->assertSessionHasErrors('role');

    expect(Role::findByName('admin', 'web'))->not->toBeNull()
        ->and(Role::findByName('customer', 'web'))->not->toBeNull();
});

it('allows admin to create a dynamic role with selected permissions', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    Permission::findOrCreate('reports.export', 'web');

    $this->actingAs($admin)
        ->post(route('roles.store'), [
            'name' => 'seo-manager',
            'permissions' => ['projects.view', 'tasks.view', 'reports.export'],
        ])
        ->assertRedirect(route('roles.index'));

    $role = Role::findByName('seo-manager', 'web');

    expect($role->hasPermissionTo('projects.view'))->toBeTrue()
        ->and($role->hasPermissionTo('tasks.view'))->toBeTrue()
        ->and($role->hasPermissionTo('reports.export'))->toBeTrue();
});
