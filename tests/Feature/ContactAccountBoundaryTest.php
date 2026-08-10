<?php

use DomainException;
use Modules\Contacts\Domain\Contracts\ContactAccountGateway;
use Modules\Contacts\Infrastructure\Models\Contact;
use Modules\Identity\Infrastructure\Models\User;
use Spatie\Permission\Models\Role;

it('keeps admin account active and on the system role', function (): void {
    $admin = User::query()->whereHas('roles', fn ($query) => $query->where('name', 'admin'))->firstOrFail();
    $accounts = app(ContactAccountGateway::class);

    expect(fn () => $accounts->save($admin->contact_id, [
        'enabled' => false,
        'role' => 'admin',
        'password' => null,
    ]))->toThrow(DomainException::class, 'system_role_immutable');

    expect(fn () => $accounts->save($admin->contact_id, [
        'enabled' => true,
        'role' => 'staff-test',
        'password' => null,
    ]))->toThrow(DomainException::class, 'system_role_immutable');

    $admin->refresh()->load('roles');

    expect($admin->is_active)->toBeTrue()
        ->and($admin->hasRole('admin'))->toBeTrue();
});

it('creates a normal account through the Identity gateway', function (): void {
    Role::findOrCreate('staff-test', 'web');
    $contact = Contact::factory()->create();
    $accounts = app(ContactAccountGateway::class);

    $accounts->save($contact->id, [
        'enabled' => true,
        'role' => 'staff-test',
        'password' => 'password123',
    ]);

    $user = User::query()->with('roles')->where('contact_id', $contact->id)->firstOrFail();

    expect($user->is_active)->toBeTrue()
        ->and($user->hasRole('staff-test'))->toBeTrue();
});
