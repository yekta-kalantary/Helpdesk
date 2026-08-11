<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Domain\Enums\ClientStatus;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Identity\Infrastructure\Models\User;

it('has the MVP client and identity schema', function (): void {
    expect(Schema::hasColumns('clients', ['name', 'description', 'status']))->toBeTrue()
        ->and(Schema::hasColumns('users', ['client_id', 'role', 'is_active', 'last_login_at']))->toBeTrue()
        ->and(Schema::hasColumn('users', 'is_admin'))->toBeFalse();
});

it('normalizes email and keeps it reserved after deactivation', function (): void {
    User::factory()->create([
        'email' => ' Customer@Example.COM ',
        'is_active' => false,
    ]);

    expect(User::query()->where('email', 'customer@example.com')->firstOrFail()->email)
        ->toBe('customer@example.com');

    expect(fn () => User::factory()->create(['email' => 'CUSTOMER@example.com']))
        ->toThrow(QueryException::class);
});

it('requires every customer to belong to exactly one client', function (): void {
    expect(fn () => User::factory()->create([
        'role' => UserRole::Customer,
        'client_id' => null,
    ]))->toThrow(DomainException::class);
});

it('allows admins without a client', function (): void {
    $admin = User::factory()->admin()->create();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->client_id)->toBeNull();
});

it('blocks login when the customer client is inactive', function (): void {
    $client = Client::query()->create([
        'name' => 'Acme',
        'status' => ClientStatus::Inactive,
    ]);

    $customer = User::factory()->create([
        'client_id' => $client->id,
        'role' => UserRole::Customer,
        'email' => 'member@example.com',
        'password' => 'password',
        'is_active' => true,
    ]);

    $this->post('/livewire/update', [])->assertStatus(419);

    expect($customer->canAuthenticate())->toBeFalse();
});
