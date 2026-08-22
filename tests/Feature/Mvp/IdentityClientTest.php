<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Clients\Application\DTOs\ClientStatusSummary;
use Modules\Clients\Domain\Enums\ClientStatus;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Application\DTOs\AccountSummary;
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

it('allows employees without a client', function (): void {
    $employee = User::factory()->employee()->create();

    expect($employee->role)->toBe(UserRole::Employee)
        ->and($employee->client_id)->toBeNull();
});

it('allows admins without a client', function (): void {
    $admin = User::factory()->admin()->create();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->client_id)->toBeNull();
});

it('finds client activation status through the public client contract', function (): void {
    $activeClient = Client::factory()->create();
    $inactiveClient = Client::factory()->inactive()->create();

    expect(app(ClientStatusQuery::class)->find($activeClient->id))
        ->toMatchObject(new ClientStatusSummary($activeClient->id, true))
        ->and(app(ClientStatusQuery::class)->find($inactiveClient->id))
        ->toMatchObject(new ClientStatusSummary($inactiveClient->id, false));
});

it('finds account authentication facts through the public identity contract', function (): void {
    $activeClient = Client::factory()->create();
    $customer = User::factory()->customer($activeClient)->create();

    expect(app(AccountDirectory::class)->find($customer->id))
        ->toMatchObject(new AccountSummary($customer->id, UserRole::Customer, true, $activeClient->id));
});

it('blocks customer authentication while its client is inactive through the eligibility service', function (): void {
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

    $account = app(AccountDirectory::class)->find($customer->id);

    expect($account)->toBeInstanceOf(AccountSummary::class)
        ->and(app(AccountAuthenticationEligibility::class)->canAuthenticate($account))->toBeFalse();

    $client->update(['status' => ClientStatus::Active]);

    expect(app(AccountAuthenticationEligibility::class)->canAuthenticate(
        app(AccountDirectory::class)->find($customer->id),
    ))->toBeTrue();
});

it('allows active employees to authenticate without a client through the eligibility service', function (): void {
    $employee = User::factory()->employee()->create();

    expect(app(AccountAuthenticationEligibility::class)->canAuthenticate(
        app(AccountDirectory::class)->find($employee->id),
    ))->toBeTrue();
});
