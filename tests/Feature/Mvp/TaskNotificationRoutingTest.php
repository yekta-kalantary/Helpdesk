<?php

use App\Notifications\ResourceChangedNotification;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
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
