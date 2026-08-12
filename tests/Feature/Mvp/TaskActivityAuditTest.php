<?php

use App\Models\Activity;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('audits the implicit assignee clear when a customer returns a task to the admin queue', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Implicit assignee clear',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    app(TaskWorkflow::class)->transitionByCustomer($customer, $task, TaskStatus::WaitingAdmin);

    $status = Activity::query()
        ->where('task_id', $task->id)
        ->where('action', 'task.status_changed')
        ->get();
    $assignee = Activity::query()
        ->where('task_id', $task->id)
        ->where('action', 'task.assignee_changed')
        ->get();

    expect($status)->toHaveCount(1)
        ->and($status->first()->actor_id)->toBe($customer->id)
        ->and($status->first()->project_id)->toBe($project->id)
        ->and($status->first()->task_id)->toBe($task->id)
        ->and($status->first()->metadata)->toMatchArray([
            'old' => TaskStatus::InProgress->value,
            'new' => TaskStatus::WaitingAdmin->value,
        ])
        ->and($assignee)->toHaveCount(1)
        ->and($assignee->first()->actor_id)->toBe($customer->id)
        ->and($assignee->first()->project_id)->toBe($project->id)
        ->and($assignee->first()->task_id)->toBe($task->id)
        ->and($assignee->first()->metadata)->toMatchArray([
            'old' => $customer->id,
            'new' => null,
        ]);
});

it('records reopened exactly once when a completed task becomes non-terminal', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Completed reopen',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
    ]);

    app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'status' => TaskStatus::Todo,
    ]);

    expect(Activity::query()->where('task_id', $task->id)->where('action', 'task.reopened')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->count())->toBe(1);
});

it('records reopened exactly once when a cancelled task becomes non-terminal', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Cancelled reopen',
        'status' => TaskStatus::Cancelled,
        'priority' => TaskPriority::Normal,
    ]);

    app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'status' => TaskStatus::Todo,
    ]);

    expect(Activity::query()->where('task_id', $task->id)->where('action', 'task.reopened')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->count())->toBe(1);
});

it('does not record reopened for terminal-to-terminal transitions and records terminal entry once', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $workflow = app(TaskWorkflow::class);

    $completedTask = $workflow->createForAdmin($admin, $project, [
        'title' => 'Completed then cancelled',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
    ]);
    $workflow->updateByAdmin($admin, $completedTask, ['status' => TaskStatus::Completed]);
    $workflow->updateByAdmin($admin, $completedTask->refresh(), ['status' => TaskStatus::Cancelled]);

    expect(Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.completed')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.cancelled')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.reopened')->count())->toBe(0)
        ->and(Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.status_changed')->count())->toBe(2);

    $cancelledTask = $workflow->createForAdmin($admin, $project, [
        'title' => 'Active then cancelled',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
    ]);
    $workflow->updateByAdmin($admin, $cancelledTask, ['status' => TaskStatus::Cancelled]);

    expect(Activity::query()->where('task_id', $cancelledTask->id)->where('action', 'task.cancelled')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $cancelledTask->id)->where('action', 'task.status_changed')->count())->toBe(1);
});
