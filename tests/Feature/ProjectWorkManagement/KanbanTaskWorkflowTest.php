<?php

use App\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;

it('allows every project member to move visible tasks regardless of assignee', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $memberA = User::factory()->customer($client)->create();
    $memberB = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $memberA, $admin);
    $memberships->add($project, $memberB, $admin);
    $open = $project->taskStatuses()->active()->where('is_done', false)->orderBy('position')->firstOrFail();
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Shared movement',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $memberA->id,
    ]);

    $task = app(TaskWorkflow::class)->changeStatus($memberB, $task, $done);

    expect($task->project_status_id)->toBe($done->id)
        ->and($task->assigned_to)->toBe($memberA->id)
        ->and($task->completed_at)->not->toBeNull();
});

it('uses done metadata for completion reopen and overdue semantics', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $openStatuses = $project->taskStatuses()->active()->where('is_done', false)->orderBy('position')->get();
    $open = $openStatuses->first();
    $otherOpen = $openStatuses->last();
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();
    $workflow = app(TaskWorkflow::class);

    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Lifecycle',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
        'due_date' => today()->subDay(),
    ]);
    expect(Task::query()->overdue()->whereKey($task)->exists())->toBeTrue();

    $task = $workflow->changeStatus($admin, $task, $otherOpen);
    expect($task->completed_at)->toBeNull();

    $task = $workflow->changeStatus($admin, $task, $done);
    expect($task->completed_at)->not->toBeNull()
        ->and(Task::query()->overdue()->whereKey($task)->exists())->toBeFalse();

    $task = $workflow->changeStatus($admin, $task, $open);
    expect($task->completed_at)->toBeNull();
});

it('defaults new tasks to first open status and permits direct creation in done', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $firstOpen = $project->taskStatuses()->active()->where('is_done', false)->orderBy('position')->firstOrFail();
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();
    $workflow = app(TaskWorkflow::class);

    $default = $workflow->createForAdmin($admin, $project, [
        'title' => 'Default',
        'priority' => TaskPriority::Normal,
    ]);
    $alreadyDone = $workflow->createForAdmin($admin, $project, [
        'title' => 'Done immediately',
        'project_status_id' => $done->id,
        'priority' => TaskPriority::Normal,
    ]);

    expect($default->project_status_id)->toBe($firstOpen->id)
        ->and($alreadyDone->project_status_id)->toBe($done->id)
        ->and($alreadyDone->completed_at)->not->toBeNull();
});

it('rejects cross project statuses and records status snapshot activity', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client, 'A');
    $other = mvpProject($client, 'B');
    $workflow = app(TaskWorkflow::class);
    $open = $project->taskStatuses()->active()->where('is_done', false)->firstOrFail();
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();
    $foreign = $other->taskStatuses()->active()->firstOrFail();
    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Audit',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => $workflow->changeStatus($admin, $task, $foreign))->toThrow(DomainException::class);

    $workflow->changeStatus($admin, $task, $done);
    $activity = Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->latest('id')->firstOrFail();

    expect($activity->metadata['previous_status_id'])->toBe($open->id)
        ->and($activity->metadata['previous_status_title_snapshot'])->toBe($open->title)
        ->and($activity->metadata['new_status_id'])->toBe($done->id)
        ->and($activity->metadata['new_status_title_snapshot'])->toBe($done->title);
});

it('requires all active tasks in done before completing a project and blocks task reopen while project is completed', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $workflow = app(TaskWorkflow::class);
    $open = $project->taskStatuses()->active()->where('is_done', false)->firstOrFail();
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();
    $task = $workflow->createForAdmin($admin, $project, ['title' => 'Must finish', 'priority' => TaskPriority::Normal]);

    expect(fn () => app(ProjectLifecycle::class)->complete($project, $admin))->toThrow(DomainException::class);

    $task = $workflow->changeStatus($admin, $task, $done);
    $project = app(ProjectLifecycle::class)->complete($project, $admin);

    expect(fn () => $workflow->changeStatus($admin, $task, $open))->toThrow(DomainException::class)
        ->and($project->status->value)->toBe('completed');
});
