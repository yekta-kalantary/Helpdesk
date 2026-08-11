<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

function mvpProject(Client $client, string $name = 'Project'): Project
{
    return Project::query()->create([
        'client_id' => $client->id,
        'name' => $name,
        'status' => ProjectStatus::Active,
    ]);
}

it('creates customer requests in the admin queue', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForCustomer($customer, $project, [
        'title' => 'Need review',
        'description' => 'Please review this request.',
    ]);

    expect($task->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($task->priority)->toBe(TaskPriority::Normal)
        ->and($task->assigned_to)->toBeNull()
        ->and($task->created_by)->toBe($customer->id)
        ->and($task->reference)->toMatch('/^TSK-[A-Z0-9]{8}$/');
});

it('uses membership rather than assignment for customer task visibility', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $viewer = User::factory()->customer($client)->create();
    $assignee = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $viewer, $admin);
    $memberships->add($project, $assignee, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Visible to all members',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    expect(Task::query()->visibleTo($viewer)->whereKey($task)->exists())->toBeTrue();
});

it('requires a valid project member when waiting for customer', function (): void {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $outsider = User::factory()->customer($clientB)->create();
    $project = mvpProject($clientA);

    expect(fn () => app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Invalid assignment',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $outsider->id,
    ]))->toThrow(DomainException::class);
});

it('clears a customer assignee when task returns to waiting admin', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Customer action',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $task = app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'status' => TaskStatus::WaitingAdmin,
    ]);

    expect($task->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($task->assigned_to)->toBeNull();
});

it('requires an active assignee for todo and in progress', function (TaskStatus $status): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    expect(fn () => app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Must be assigned',
        'status' => $status,
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]))->toThrow(DomainException::class);
})->with([TaskStatus::Todo, TaskStatus::InProgress]);

it('sets completed at and clears it when reopened', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Lifecycle',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);

    expect($task->completed_at)->not->toBeNull();

    $task = app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'status' => TaskStatus::WaitingAdmin,
    ]);

    expect($task->completed_at)->toBeNull();
});

it('only lets an assigned customer perform customer transitions', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $other = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $customer, $admin);
    $memberships->add($project, $other, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Assigned work',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    expect(fn () => app(TaskWorkflow::class)->transitionByCustomer($other, $task, TaskStatus::InProgress))
        ->toThrow(DomainException::class);

    $task = app(TaskWorkflow::class)->transitionByCustomer($customer, $task, TaskStatus::WaitingAdmin);

    expect($task->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($task->assigned_to)->toBeNull();
});

it('keeps task reference and project immutable', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $projectA = mvpProject($client, 'A');
    $projectB = mvpProject($client, 'B');
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $projectA, [
        'title' => 'Immutable boundaries',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);

    expect(fn () => $task->update(['reference' => 'TSK-CHANGED']))
        ->toThrow(DomainException::class)
        ->and(fn () => $task->update(['project_id' => $projectB->id]))
        ->toThrow(DomainException::class);
});
