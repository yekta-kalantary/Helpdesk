<?php

use App\Notifications\ResourceChangedNotification;
use App\Support\CustomerAssignmentRequeuer;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

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
        'project_status_id' => mvpOpenStatus($project, 1)->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    Notification::assertSentTo($creator, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($assignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($unrelatedMember, ResourceChangedNotification::class);
    Notification::assertNotSentTo($admin, ResourceChangedNotification::class);
});

it('routes an admin-created assigned task only to its assignee', function (): void {
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
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    Notification::assertSentTo($assignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($unrelatedMember, ResourceChangedNotification::class);
    Notification::assertNotSentTo($admin, ResourceChangedNotification::class);
});

it('does not recreate fixed admin queue routing for unassigned tasks', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $actor = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $actor);

    Notification::fake();

    app(TaskWorkflow::class)->createForAdmin($actor, $project, [
        'title' => 'Unassigned task',
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);

    Notification::assertNotSentTo($otherAdmin, ResourceChangedNotification::class);
    Notification::assertNotSentTo($member, ResourceChangedNotification::class);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
});

it('routes assignment-only changes to the creator and new assignee but not the old assignee', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->customer($client)->create();
    $oldAssignee = User::factory()->customer($client)->create();
    $newAssignee = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    foreach ([$creator, $oldAssignee, $newAssignee] as $member) {
        $memberships->add($project, $member, $admin);
    }

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, ['title' => 'Reassign task']);
    app(TaskWorkflow::class)->updateByAdmin($admin, $task, ['assigned_to' => $oldAssignee->id]);

    Notification::fake();

    app(TaskWorkflow::class)->updateByAdmin($admin, $task->refresh(), ['assigned_to' => $newAssignee->id]);

    Notification::assertSentTo($creator, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($newAssignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($oldAssignee, ResourceChangedNotification::class);
    Notification::assertNotSentTo($admin, ResourceChangedNotification::class);
});

it('routes member-driven status changes to task participants and excludes the actor', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->customer($client)->create();
    $assignee = User::factory()->customer($client)->create();
    $actor = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    foreach ([$creator, $assignee, $actor] as $member) {
        $memberships->add($project, $member, $admin);
    }

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, ['title' => 'Member move']);
    $task = app(TaskWorkflow::class)->updateByAdmin($admin, $task, ['assigned_to' => $assignee->id]);
    Notification::fake();

    app(TaskWorkflow::class)->changeStatus($actor, $task, mvpOpenStatus($project, 1));

    Notification::assertSentTo($creator, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($assignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
});

it('routes comments to creator and assignee while excluding the commenter', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->customer($client)->create();
    $assignee = User::factory()->customer($client)->create();
    $commenter = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    foreach ([$creator, $assignee, $commenter] as $member) {
        $memberships->add($project, $member, $admin);
    }

    $task = app(TaskWorkflow::class)->createForCustomer($creator, $project, ['title' => 'Comment routing']);
    $task = app(TaskWorkflow::class)->updateByAdmin($admin, $task, ['assigned_to' => $assignee->id]);
    Notification::fake();

    app(TaskCollaboration::class)->comment($commenter, $task, 'Hello', []);

    Notification::assertSentTo($creator, ResourceChangedNotification::class, 1);
    Notification::assertSentTo($assignee, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($commenter, ResourceChangedNotification::class);
});

it('notifies active admins when access changes automatically release a customer assignment', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $actor = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->inactive()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $actor);

    $task = app(TaskWorkflow::class)->createForAdmin($actor, $project, [
        'title' => 'Assignment needs release',
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);
    Notification::fake();

    app(CustomerAssignmentRequeuer::class)->requeue($customer, $actor, $project);

    Notification::assertSentTo($otherAdmin, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($inactiveAdmin, ResourceChangedNotification::class);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
    expect($task->refresh()->assigned_to)->toBeNull();
});
