<?php

use App\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('releases open task assignments before removing customer project membership without changing workflow status', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $customer, $admin);
    $open = mvpOpenStatus($project, 1);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Needs customer action',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $memberships->remove($project, $customer, $admin);
    $task->refresh();

    expect($task->project_status_id)->toBe($open->id)
        ->and($task->assigned_to)->toBeNull()
        ->and($project->hasActiveMember($customer))->toBeFalse()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->exists())->toBeFalse()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->exists())->toBeTrue();
});

it('preserves historical assignee on done tasks when membership is removed', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $customer, $admin);
    $done = mvpDoneStatus($project);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Historical assignment',
        'project_status_id' => $done->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $memberships->remove($project, $customer, $admin);

    expect($task->refresh()->project_status_id)->toBe($done->id)
        ->and($task->assigned_to)->toBe($customer->id)
        ->and($task->completed_at)->not->toBeNull();
});
