<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;

it('does not grant project visibility from client ownership alone', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = Project::query()->create([
        'client_id' => $client->id,
        'name' => 'Private project',
        'status' => ProjectStatus::Active,
    ]);

    expect(Project::query()->visibleTo($customer)->whereKey($project)->exists())->toBeFalse();
});

it('grants visibility only while membership is active', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = Project::query()->create([
        'client_id' => $client->id,
        'name' => 'Member project',
        'status' => ProjectStatus::Active,
    ]);

    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $customer, $admin);

    expect(Project::query()->visibleTo($customer)->whereKey($project)->exists())->toBeTrue();

    $manager->remove($project, $customer, $admin);

    expect(Project::query()->visibleTo($customer)->whereKey($project)->exists())->toBeFalse();
});

it('grants employees visibility only through active same-client membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->employee($client)->create();
    $project = mvpProject($client, 'Employee project');
    $manager = app(ProjectMembershipManager::class);

    expect(Project::query()->visibleTo($employee)->whereKey($project)->exists())->toBeFalse();

    $manager->add($project, $employee, $admin);

    expect(Project::query()->visibleTo($employee)->whereKey($project)->exists())->toBeTrue();

    $manager->remove($project, $employee, $admin);

    expect(Project::query()->visibleTo($employee)->whereKey($project)->exists())->toBeFalse();
});

it('keeps customer and employee visibility equivalent for equivalent memberships', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $employee = User::factory()->employee($client)->create();
    $project = mvpProject($client, 'Equivalent access');
    $manager = app(ProjectMembershipManager::class);

    $manager->add($project, $customer, $admin);
    $manager->add($project, $employee, $admin);

    expect(Project::query()->visibleTo($customer)->whereKey($project)->exists())
        ->toBe(Project::query()->visibleTo($employee)->whereKey($project)->exists());
});

it('gives admins full visibility without membership or a client', function (): void {
    $admin = User::factory()->admin()->create();
    $project = mvpProject(Client::factory()->create(), 'Admin project');

    expect(Project::query()->visibleTo($admin)->whereKey($project)->exists())->toBeTrue();
});

it('reactivates the same membership row and preserves lifecycle history', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = Project::query()->create([
        'client_id' => $client->id,
        'name' => 'Lifecycle project',
        'status' => ProjectStatus::Active,
    ]);

    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $customer, $admin);
    $firstJoinedAt = DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->value('joined_at');

    $manager->remove($project, $customer, $admin);

    expect(DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->value('removed_at'))->not->toBeNull();

    $manager->add($project, $customer, $admin);

    $membership = DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->first();

    expect(DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->count())->toBe(1)
        ->and($membership->removed_at)->toBeNull()
        ->and($membership->joined_at)->not->toBeNull()
        ->and($firstJoinedAt)->not->toBeNull();
});

it('rejects cross-client membership and inactive customers', function (): void {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customerB = User::factory()->customer($clientB)->create();
    $inactiveCustomerA = User::factory()->customer($clientA)->inactive()->create();
    $project = Project::query()->create([
        'client_id' => $clientA->id,
        'name' => 'Client A project',
        'status' => ProjectStatus::Active,
    ]);

    $manager = app(ProjectMembershipManager::class);

    expect(fn () => $manager->add($project, $customerB, $admin))->toThrow(DomainException::class)
        ->and(fn () => $manager->add($project, $inactiveCustomerA, $admin))->toThrow(DomainException::class);
});

it('rejects cross-client and inactive employee memberships', function (): void {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employeeB = User::factory()->employee($clientB)->create();
    $inactiveEmployeeA = User::factory()->employee($clientA)->inactive()->create();
    $project = mvpProject($clientA, 'Client A project');
    $manager = app(ProjectMembershipManager::class);

    expect(fn () => $manager->add($project, $employeeB, $admin))->toThrow(DomainException::class)
        ->and(fn () => $manager->add($project, $inactiveEmployeeA, $admin))->toThrow(DomainException::class);
});

it('keeps project client immutable after creation', function (): void {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $project = Project::query()->create([
        'client_id' => $clientA->id,
        'name' => 'Immutable client',
        'status' => ProjectStatus::Active,
    ]);

    expect(fn () => $project->update(['client_id' => $clientB->id]))
        ->toThrow(DomainException::class);
});
