<?php

use App\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('requeues open tasks assigned to a customer before removing their project membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Needs customer action',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $memberships->remove($project, $customer, $admin);
    $task->refresh();

    expect($task->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($task->assigned_to)->toBeNull()
        ->and($project->hasActiveMember($customer))->toBeFalse()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->exists())->toBeTrue()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->exists())->toBeTrue();
});

it('preserves historical assignee on terminal tasks when membership is removed', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Historical assignment',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $memberships->remove($project, $customer, $admin);

    expect($task->refresh()->status)->toBe(TaskStatus::Completed)
        ->and($task->assigned_to)->toBe($customer->id)
        ->and($task->completed_at)->not->toBeNull();
});
