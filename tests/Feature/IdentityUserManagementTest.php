<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

it('allows administrators to view users and active client options', function (): void {
    $admin = User::factory()->admin()->create();
    $activeClient = Client::factory()->create(['name' => 'Active client']);
    Client::factory()->inactive()->create(['name' => 'Inactive client']);

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Identity/Users/Index')
            ->has('users.data')
            ->where('clients', fn ($clients) => collect($clients)->contains('id', $activeClient->id))
            ->where('clients', fn ($clients) => ! collect($clients)->contains('name', 'Inactive client'))
            ->where('roles', fn ($roles) => collect($roles)->pluck('value')->contains('admin')));
});

it('rejects non-admin users from user management', function (): void {
    $employee = User::factory()->employee()->create();

    $this->actingAs($employee)
        ->get(route('users.index'))
        ->assertForbidden();
});

it('creates a customer with a manually assigned password', function (): void {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'mobile' => '+15551234567',
            'role' => 'customer',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'manual',
            'password' => 'manual-password',
            'password_confirmation' => 'manual-password',
        ])
        ->assertRedirect(route('users.index'));

    $user = User::query()->where('email', 'ada@example.test')->firstOrFail();

    expect($user->role->value)->toBe('customer')
        ->and($user->client_id)->toBe($client->id)
        ->and($user->is_active)->toBeTrue()
        ->and(Hash::check('manual-password', $user->password))->toBeTrue();
});

it('requires a client for customer users', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'customer',
            'is_active' => true,
            'password_mode' => 'manual',
            'password' => 'manual-password',
            'password_confirmation' => 'manual-password',
        ])
        ->assertSessionHasErrors('client_id');
});

it('rejects an inactive client for customer users', function (): void {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->inactive()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'customer',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'manual',
            'password' => 'manual-password',
            'password_confirmation' => 'manual-password',
        ])
        ->assertSessionHasErrors('client_id');
});

it('rejects a client assignment for non-customer users', function (): void {
    $employee = User::factory()->employee()->create();
    $client = Client::factory()->create();

    $this->actingAs($employee)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'employee',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'manual',
            'password' => 'manual-password',
            'password_confirmation' => 'manual-password',
        ])
        ->assertSessionHasErrors('client_id');
});

it('rejects a client assignment for admin users', function (): void {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'admin',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'manual',
            'password' => 'manual-password',
            'password_confirmation' => 'manual-password',
        ])
        ->assertSessionHasErrors('client_id');
});

it('requires a password for manual password mode', function (): void {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'customer',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'manual',
            'password_confirmation' => '',
        ])
        ->assertSessionHasErrors('password');
});

it('rejects a manual password confirmation mismatch', function (): void {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'customer',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'manual',
            'password' => 'manual-password',
            'password_confirmation' => 'different-password',
        ])
        ->assertSessionHasErrors('password');
});

it('rejects a manual password shorter than the minimum length', function (): void {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'customer',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'manual',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors('password');
});

it('prohibits a submitted password in email password mode', function (): void {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'customer',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'email',
            'password' => 'submitted-password',
            'password_confirmation' => 'submitted-password',
        ])
        ->assertSessionHasErrors('password');
});

it('creates an inactive user and dispatches a reset link in email mode', function (): void {
    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => 'ada@example.test'])
        ->andReturn(Password::RESET_LINK_SENT);

    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'role' => 'customer',
            'client_id' => $client->id,
            'is_active' => true,
            'password_mode' => 'email',
        ])
        ->assertRedirect(route('users.index'));

    $user = User::query()->where('email', 'ada@example.test')->firstOrFail();

    expect($user->password)->toBeNull()
        ->and($user->is_active)->toBeFalse();
});
