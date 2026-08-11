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
