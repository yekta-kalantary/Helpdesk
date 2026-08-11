<?php

use App\Models\Activity;
use App\Notifications\ResourceChangedNotification;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('records task creation and state changes without sensitive metadata', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Audit me',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'priority' => TaskPriority::High,
        'status' => TaskStatus::WaitingAdmin,
    ]);

    expect(Activity::query()->where('task_id', $task->id)->where('action', 'task.created')->exists())->toBeTrue()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.priority_changed')->exists())->toBeTrue();

    $serialized = Activity::query()->where('task_id', $task->id)->get()->pluck('metadata')->toJson();

    expect(strtolower($serialized))->not->toContain('password', 'token', 'secret');
});

it('notifies every active admin when a customer creates an admin queue task', function (): void {
    Notification::fake();
    $client = Client::factory()->create();
    $adminOne = User::factory()->admin()->create();
    $adminTwo = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->inactive()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $adminOne);

    app(TaskWorkflow::class)->createForCustomer($customer, $project, [
        'title' => 'Queue task',
    ]);

    Notification::assertSentTo([$adminOne, $adminTwo], ResourceChangedNotification::class);
    Notification::assertNotSentTo($inactiveAdmin, ResourceChangedNotification::class);
});

it('records membership add and remove events and notifies the affected customer', function (): void {
    Notification::fake();
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $manager = app(ProjectMembershipManager::class);

    $manager->add($project, $customer, $admin);
    $manager->remove($project, $customer, $admin);

    expect(Activity::query()->where('project_id', $project->id)->where('action', 'membership.added')->exists())->toBeTrue()
        ->and(Activity::query()->where('project_id', $project->id)->where('action', 'membership.removed')->exists())->toBeTrue();

    Notification::assertSentTo($customer, ResourceChangedNotification::class, 2);
});
