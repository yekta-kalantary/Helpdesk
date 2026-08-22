<?php

use App\Models\OutboxMessage;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\Contracts\ProjectAccessQuery;
use Modules\Projects\Application\Events\ProjectMembershipRemovedV1;
use Modules\Projects\Application\Events\ProjectTaskStatusChangedV1;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\ProjectWorkflowManager;
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

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $customer->id))->toBeFalse();
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
    $manager->add($project, $customer->id, $admin->id);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $customer->id))->toBeTrue();

    $manager->remove($project, $customer->id, $admin->id);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $customer->id))->toBeFalse();
});

it('revokes customer project visibility when its client is deactivated', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Client activation project');

    app(ProjectMembershipManager::class)->add($project, $customer->id, $admin->id);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $customer->id))->toBeTrue();

    $client->update(['status' => 'inactive']);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $customer->id))->toBeFalse();
});

it('grants clientless employees visibility only through active membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->employee()->create();
    $project = mvpProject($client, 'Employee project');
    $nonMemberProject = mvpProject($client, 'Non-member project');
    $manager = app(ProjectMembershipManager::class);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $employee->id))->toBeFalse();
    expect(app(ProjectAccessQuery::class)->canAccessProject($nonMemberProject->id, $employee->id))->toBeFalse();

    $manager->add($project, $employee->id, $admin->id);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $employee->id))->toBeTrue();
    expect(app(ProjectAccessQuery::class)->canAccessProject($nonMemberProject->id, $employee->id))->toBeFalse();

    $manager->remove($project, $employee->id, $admin->id);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $employee->id))->toBeFalse();
});

it('keeps customer and employee visibility equivalent for equivalent memberships', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $employee = User::factory()->employee()->create();
    $project = mvpProject($client, 'Equivalent access');
    $manager = app(ProjectMembershipManager::class);

    $manager->add($project, $customer->id, $admin->id);
    $manager->add($project, $employee->id, $admin->id);

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $customer->id))
        ->toBe(app(ProjectAccessQuery::class)->canAccessProject($project->id, $employee->id));
});

it('gives admins full visibility without membership or a client', function (): void {
    $admin = User::factory()->admin()->create();
    $project = mvpProject(Client::factory()->create(), 'Admin project');

    expect(app(ProjectAccessQuery::class)->canAccessProject($project->id, $admin->id))->toBeTrue();
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
    $manager->add($project, $customer->id, $admin->id);
    $firstJoinedAt = DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->value('joined_at');

    $manager->remove($project, $customer->id, $admin->id);

    expect(DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->value('removed_at'))->not->toBeNull();

    $manager->add($project, $customer->id, $admin->id);

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

it('records an immutable event when a membership is removed', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Membership event project');
    $manager = app(ProjectMembershipManager::class);

    $manager->add($project, $member->id, $admin->id);
    $manager->remove($project, $member->id, $admin->id);

    $event = OutboxMessage::query()->where('event_type', ProjectMembershipRemovedV1::class)->sole();

    expect($event->payload)->toMatchArray([
        'project_id' => $project->id,
        'account_id' => $member->id,
        'actor_id' => $admin->id,
    ]);
});

it('records an immutable event when the done status changes', function (): void {
    $admin = User::factory()->admin()->create();
    $project = mvpProject(Client::factory()->create(), 'Status event project');
    $status = mvpOpenStatus($project);

    app(ProjectWorkflowManager::class)->setDone($admin->id, $status);

    $event = OutboxMessage::query()->where('event_type', ProjectTaskStatusChangedV1::class)->sole();

    expect($event->payload)->toMatchArray([
        'project_id' => $project->id,
        'project_task_status_id' => $status->id,
        'is_done' => true,
        'actor_id' => $admin->id,
    ]);
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

    expect(fn () => $manager->add($project, $customerB->id, $admin->id))->toThrow(DomainException::class)
        ->and(fn () => $manager->add($project, $inactiveCustomerA->id, $admin->id))->toThrow(DomainException::class);
});

it('allows clientless employees and rejects inactive employee memberships', function (): void {
    $clientA = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->employee()->create();
    $inactiveEmployee = User::factory()->employee()->inactive()->create();
    $project = mvpProject($clientA, 'Client A project');
    $manager = app(ProjectMembershipManager::class);

    $manager->add($project, $employee->id, $admin->id);

    expect(fn () => $manager->add($project, $inactiveEmployee->id, $admin->id))->toThrow(DomainException::class);
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
