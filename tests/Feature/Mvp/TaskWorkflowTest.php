<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;

it('creates customer requests in the first open project status as unassigned root tasks', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForCustomer($customer, $project, [
        'title' => 'Need review',
        'description' => 'Please review this request.',
    ]);

    expect($task->project_status_id)->toBe(mvpOpenStatus($project)->id)
        ->and($task->priority)->toBe(TaskPriority::Normal)
        ->and($task->assigned_to)->toBeNull()
        ->and($task->work_group_id)->toBeNull()
        ->and($task->created_by)->toBe($customer->id)
        ->and($task->reference)->toMatch('/^TSK-[A-Z0-9]{8}$/');
});

it('rejects attempts to place customer-created tasks directly in a Work Group', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $group = app(WorkGroupManager::class)->create($admin, $project, ['title' => 'Admin structure']);

    expect(fn () => app(TaskWorkflow::class)->createForCustomer($customer, $project, [
        'title' => 'Forged grouped request',
        'work_group_id' => $group->id,
    ]))->toThrow(DomainException::class);
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
        'project_status_id' => mvpOpenStatus($project, 1)->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    expect(Task::query()->visibleTo($viewer)->whereKey($task)->exists())->toBeTrue();
});

it('marks only past tasks outside Done as overdue', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $workflow = app(TaskWorkflow::class);

    $yesterday = $workflow->createForAdmin($admin, $project, [
        'title' => 'Yesterday',
        'priority' => TaskPriority::Normal,
        'due_date' => today()->subDay(),
    ]);
    $today = $workflow->createForAdmin($admin, $project, [
        'title' => 'Today',
        'priority' => TaskPriority::Normal,
        'due_date' => today(),
    ]);
    $tomorrow = $workflow->createForAdmin($admin, $project, [
        'title' => 'Tomorrow',
        'priority' => TaskPriority::Normal,
        'due_date' => today()->addDay(),
    ]);
    $done = $workflow->createForAdmin($admin, $project, [
        'title' => 'Done',
        'project_status_id' => mvpDoneStatus($project)->id,
        'priority' => TaskPriority::Normal,
        'due_date' => today()->subDay(),
    ]);

    expect(Task::query()->overdue()->pluck('id')->all())
        ->toBe([$yesterday->id])
        ->and(Task::query()->overdue()->whereKey($today)->exists())->toBeFalse()
        ->and(Task::query()->overdue()->whereKey($tomorrow)->exists())->toBeFalse()
        ->and(Task::query()->overdue()->whereKey($done)->exists())->toBeFalse();
});

it('requires every customer assignee to be an active member of the same project regardless of status', function (): void {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $outsider = User::factory()->customer($clientB)->create();
    $project = mvpProject($clientA);

    expect(fn () => app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Invalid assignment',
        'project_status_id' => mvpOpenStatus($project, 1)->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $outsider->id,
    ]))->toThrow(DomainException::class);
});

it('keeps assignment independent from project status changes and permits unassigned open tasks', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $openA = mvpOpenStatus($project);
    $openB = mvpOpenStatus($project, 1);

    $unassigned = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Unassigned open work',
        'project_status_id' => $openA->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);
    $assigned = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Assigned work',
        'project_status_id' => $openA->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $assigned = app(TaskWorkflow::class)->changeStatus($admin, $assigned, $openB);

    expect($unassigned->assigned_to)->toBeNull()
        ->and($assigned->project_status_id)->toBe($openB->id)
        ->and($assigned->assigned_to)->toBe($customer->id);
});

it('sets completed at on entry to Done and clears it only when reopened', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $workflow = app(TaskWorkflow::class);
    $open = mvpOpenStatus($project);
    $otherOpen = mvpOpenStatus($project, 1);
    $done = mvpDoneStatus($project);

    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Lifecycle',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
    ]);

    $task = $workflow->changeStatus($admin, $task, $otherOpen);
    expect($task->completed_at)->toBeNull();

    $task = $workflow->changeStatus($admin, $task, $done);
    expect($task->completed_at)->not->toBeNull();

    $task = $workflow->changeStatus($admin, $task, $open);
    expect($task->completed_at)->toBeNull();
});

it('lets any project member perform customer status transitions regardless of assignee', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $other = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $customer, $admin);
    $memberships->add($project, $other, $admin);
    $openA = mvpOpenStatus($project);
    $openB = mvpOpenStatus($project, 1);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Assigned work',
        'project_status_id' => $openA->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $task = app(TaskWorkflow::class)->transitionByCustomer($other, $task, $openB);

    expect($task->project_status_id)->toBe($openB->id)
        ->and($task->assigned_to)->toBe($customer->id);
});

it('keeps task reference and project immutable', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $projectA = mvpProject($client, 'A');
    $projectB = mvpProject($client, 'B');
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $projectA, [
        'title' => 'Immutable boundaries',
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => $task->update(['reference' => 'TSK-CHANGED']))
        ->toThrow(DomainException::class)
        ->and(fn () => $task->update(['project_id' => $projectB->id]))
        ->toThrow(DomainException::class);
});
