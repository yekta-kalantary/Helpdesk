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

    $status = Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->get();
    $assignee = Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->get();

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

it('does not audit an assignee change when a customer transition keeps the same assignee', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Stable assignee',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    app(TaskWorkflow::class)->transitionByCustomer($customer, $task, TaskStatus::Completed);

    expect(Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->count())->toBe(0)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.completed')->count())->toBe(1);
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
        'status' => TaskStatus::WaitingAdmin,
    ]);

    $status = Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->get();
    $reopened = Activity::query()->where('task_id', $task->id)->where('action', 'task.reopened')->get();

    expect($status)->toHaveCount(1)
        ->and($status->first()->actor_id)->toBe($admin->id)
        ->and($status->first()->project_id)->toBe($project->id)
        ->and($status->first()->task_id)->toBe($task->id)
        ->and($status->first()->metadata)->toMatchArray([
            'old' => TaskStatus::Completed->value,
            'new' => TaskStatus::WaitingAdmin->value,
        ])
        ->and($reopened)->toHaveCount(1)
        ->and($reopened->first()->actor_id)->toBe($admin->id)
        ->and($reopened->first()->project_id)->toBe($project->id)
        ->and($reopened->first()->task_id)->toBe($task->id)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->count())->toBe(0);
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
        'status' => TaskStatus::WaitingAdmin,
    ]);

    $status = Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->get();
    $reopened = Activity::query()->where('task_id', $task->id)->where('action', 'task.reopened')->get();

    expect($status)->toHaveCount(1)
        ->and($status->first()->actor_id)->toBe($admin->id)
        ->and($status->first()->project_id)->toBe($project->id)
        ->and($status->first()->task_id)->toBe($task->id)
        ->and($status->first()->metadata)->toMatchArray([
            'old' => TaskStatus::Cancelled->value,
            'new' => TaskStatus::WaitingAdmin->value,
        ])
        ->and($reopened)->toHaveCount(1)
        ->and($reopened->first()->actor_id)->toBe($admin->id)
        ->and($reopened->first()->project_id)->toBe($project->id)
        ->and($reopened->first()->task_id)->toBe($task->id)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->count())->toBe(0);
});

it('does not record reopened for terminal-to-terminal transitions and records terminal entry once', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $workflow = app(TaskWorkflow::class);

    $completedTask = $workflow->createForAdmin($admin, $project, [
        'title' => 'Completed then cancelled',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $workflow->updateByAdmin($admin, $completedTask, ['status' => TaskStatus::Completed]);
    $workflow->updateByAdmin($admin, $completedTask->refresh(), ['status' => TaskStatus::Cancelled]);

    $completed = Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.completed')->get();
    $cancelled = Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.cancelled')->get();
    $statusChanges = Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.status_changed')->orderBy('id')->get();

    expect($completed)->toHaveCount(1)
        ->and($completed->first()->actor_id)->toBe($admin->id)
        ->and($completed->first()->project_id)->toBe($project->id)
        ->and($completed->first()->task_id)->toBe($completedTask->id)
        ->and($cancelled)->toHaveCount(1)
        ->and($cancelled->first()->actor_id)->toBe($admin->id)
        ->and($cancelled->first()->project_id)->toBe($project->id)
        ->and($cancelled->first()->task_id)->toBe($completedTask->id)
        ->and($statusChanges)->toHaveCount(2)
        ->and($statusChanges[0]->metadata)->toMatchArray([
            'old' => TaskStatus::WaitingAdmin->value,
            'new' => TaskStatus::Completed->value,
        ])
        ->and($statusChanges[1]->metadata)->toMatchArray([
            'old' => TaskStatus::Completed->value,
            'new' => TaskStatus::Cancelled->value,
        ])
        ->and(Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.reopened')->count())->toBe(0)
        ->and(Activity::query()->where('task_id', $completedTask->id)->where('action', 'task.assignee_changed')->count())->toBe(0);

    $cancelledTask = $workflow->createForAdmin($admin, $project, [
        'title' => 'Active then cancelled',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $workflow->updateByAdmin($admin, $cancelledTask, ['status' => TaskStatus::Cancelled]);

    $cancelledEntry = Activity::query()->where('task_id', $cancelledTask->id)->where('action', 'task.cancelled')->get();
    $cancelledStatus = Activity::query()->where('task_id', $cancelledTask->id)->where('action', 'task.status_changed')->get();

    expect($cancelledEntry)->toHaveCount(1)
        ->and($cancelledEntry->first()->actor_id)->toBe($admin->id)
        ->and($cancelledEntry->first()->project_id)->toBe($project->id)
        ->and($cancelledEntry->first()->task_id)->toBe($cancelledTask->id)
        ->and($cancelledStatus)->toHaveCount(1)
        ->and($cancelledStatus->first()->metadata)->toMatchArray([
            'old' => TaskStatus::WaitingAdmin->value,
            'new' => TaskStatus::Cancelled->value,
        ]);
});
