<?php

use App\Notifications\ResourceChangedNotification;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('routes task status changes only to the creator and current assignee', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->customer($client)->create();
    $assignee = User::factory()->customer($client)->create();
    $unrelatedMember = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $creator, $admin);
    $memberships->add($project, $assignee, $admin);
    $memberships->add($project, $unrelatedMember, $admin);

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, [
        'title' => 'Route status notifications',
    ]);

    Notification::fake();

    app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    Notification::assertSentTo($creator, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($assignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($unrelatedMember, ResourceChangedNotification::class);
    Notification::assertNotSentTo($admin, ResourceChangedNotification::class);
});

it('routes an admin created assigned task only to its assignee', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $assignee = User::factory()->customer($client)->create();
    $unrelatedMember = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $assignee, $admin);
    $memberships->add($project, $unrelatedMember, $admin);

    Notification::fake();

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Assigned task',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    Notification::assertSentTo($assignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($unrelatedMember, ResourceChangedNotification::class);
    Notification::assertNotSentTo($admin, ResourceChangedNotification::class);
});

it('routes an unassigned admin queue task to every active admin only', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $actor = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $actor);

    Notification::fake();

    app(TaskWorkflow::class)->createForAdmin($actor, $project, [
        'title' => 'Admin queue task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);

    Notification::assertSentTo($otherAdmin, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($inactiveAdmin, ResourceChangedNotification::class);
    Notification::assertNotSentTo($member, ResourceChangedNotification::class);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
});

it('routes assignment-only changes only to the new assignee', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->customer($client)->create();
    $oldAssignee = User::factory()->customer($client)->create();
    $newAssignee = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $creator, $admin);
    $memberships->add($project, $oldAssignee, $admin);
    $memberships->add($project, $newAssignee, $admin);

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, [
        'title' => 'Reassign task',
    ]);
    app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'status' => TaskStatus::WaitingCustomer,
        'assigned_to' => $oldAssignee->id,
    ]);

    Notification::fake();

    app(TaskWorkflow::class)->updateByAdmin($admin, $task->refresh(), [
        'assigned_to' => $newAssignee->id,
    ]);

    Notification::assertSentTo($newAssignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($creator, ResourceChangedNotification::class);
    Notification::assertNotSentTo($oldAssignee, ResourceChangedNotification::class);
    Notification::assertNotSentTo($admin, ResourceChangedNotification::class);
});

it('adds active admins when a status change enters an unassigned admin queue', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $actor = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
    $creator = User::factory()->customer($client)->create();
    $assignee = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $creator, $actor);
    $memberships->add($project, $assignee, $actor);

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, [
        'title' => 'Return to admin queue',
    ]);
    app(TaskWorkflow::class)->updateByAdmin($actor, $task, [
        'status' => TaskStatus::WaitingCustomer,
        'assigned_to' => $assignee->id,
    ]);

    Notification::fake();

    app(TaskWorkflow::class)->updateByAdmin($actor, $task->refresh(), [
        'status' => TaskStatus::WaitingAdmin,
    ]);

    Notification::assertSentTo($creator, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($otherAdmin, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($inactiveAdmin, ResourceChangedNotification::class);
    Notification::assertNotSentTo($assignee, ResourceChangedNotification::class);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
});

it('routes comments only to the creator and current assignee', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->customer($client)->create();
    $assignee = User::factory()->customer($client)->create();
    $commenter = User::factory()->customer($client)->create();
    $observer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    foreach ([$creator, $assignee, $commenter, $observer] as $member) {
        $memberships->add($project, $member, $admin);
    }

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, [
        'title' => 'Comment routing',
    ]);
    app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'status' => TaskStatus::WaitingCustomer,
        'assigned_to' => $assignee->id,
    ]);

    Notification::fake();

    app(TaskCollaboration::class)->comment($commenter, $task->refresh(), 'Status update', []);

    Notification::assertSentTo($creator, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($assignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($commenter, ResourceChangedNotification::class);
    Notification::assertNotSentTo($observer, ResourceChangedNotification::class);
});

it('routes admin queue comments to active admins without unrelated project members', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $adminA = User::factory()->admin()->create();
    $adminB = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
    $creator = User::factory()->customer($client)->create();
    $observer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $creator, $adminA);
    $memberships->add($project, $observer, $adminA);

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, [
        'title' => 'Admin queue comment',
    ]);

    Notification::fake();

    app(TaskCollaboration::class)->comment($creator, $task, 'Needs admin input', []);

    Notification::assertSentTo($adminA, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($adminB, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($inactiveAdmin, ResourceChangedNotification::class);
    Notification::assertNotSentTo($creator, ResourceChangedNotification::class);
    Notification::assertNotSentTo($observer, ResourceChangedNotification::class);
});

it('routes a customer transition into admin queue once to each active admin', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $adminA = User::factory()->admin()->create();
    $adminB = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
    $assignee = User::factory()->customer($client)->create();
    $observer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $assignee, $adminA);
    $memberships->add($project, $observer, $adminA);

    $task = app(TaskWorkflow::class)->createForAdmin($adminA, $project, [
        'title' => 'Customer transition',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    Notification::fake();

    app(TaskWorkflow::class)->transitionByCustomer($assignee, $task, TaskStatus::WaitingAdmin);

    Notification::assertSentTo($adminA, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($adminB, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($inactiveAdmin, ResourceChangedNotification::class);
    Notification::assertNotSentTo($assignee, ResourceChangedNotification::class);
    Notification::assertNotSentTo($observer, ResourceChangedNotification::class);
});

it('routes automatic membership-removal requeues to active admins', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $actor = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
    $assignee = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $assignee, $actor);

    $task = app(TaskWorkflow::class)->createForAdmin($actor, $project, [
        'title' => 'Membership requeue',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    Notification::fake();

    $memberships->remove($project, $assignee, $actor);

    expect($task->refresh()->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($task->assigned_to)->toBeNull();
    Notification::assertSentTo($otherAdmin, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($inactiveAdmin, ResourceChangedNotification::class);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
});
