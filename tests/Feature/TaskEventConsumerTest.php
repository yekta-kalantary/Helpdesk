<?php

use Illuminate\Support\Str;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\Events\ProjectMembershipRemovedV1;
use Modules\Projects\Application\Events\ProjectTaskStatusChangedV1;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\Consumers\ProjectMembershipRemovedConsumer;
use Modules\Tasks\Application\Consumers\ProjectTaskStatusChangedConsumer;
use Modules\Tasks\Infrastructure\Models\Task;

it('unassigns removed project members once for duplicate event delivery', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member->id, $admin->id);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'assigned_to' => $member->id,
        'title' => 'Assigned task',
    ]);
    $event = new ProjectMembershipRemovedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        accountId: $member->id,
        actorId: $admin->id,
    );

    app(ProjectMembershipRemovedConsumer::class)->handle($event);
    app(ProjectMembershipRemovedConsumer::class)->handle($event);

    expect($task->fresh()->assigned_to)->toBeNull()
        ->and(Task::query()->whereKey($task)->count())->toBe(1);
});

it('synchronizes task completion when a project status changes done state', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $status = mvpOpenStatus($project);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => $status->id,
        'created_by' => $admin->id,
        'title' => 'Status task',
    ]);
    $doneEvent = new ProjectTaskStatusChangedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        projectTaskStatusId: $status->id,
        previousDoneStatusId: null,
        isDone: true,
        actorId: $admin->id,
    );

    app(ProjectTaskStatusChangedConsumer::class)->handle($doneEvent);

    expect($task->fresh()->completed_at)->not->toBeNull();

    $reopenedEvent = new ProjectTaskStatusChangedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        projectTaskStatusId: mvpDoneStatus($project)->id,
        previousDoneStatusId: $status->id,
        isDone: true,
        actorId: $admin->id,
    );

    app(ProjectTaskStatusChangedConsumer::class)->handle($reopenedEvent);
    app(ProjectTaskStatusChangedConsumer::class)->handle($reopenedEvent);

    expect($task->fresh()->completed_at)->toBeNull()
        ->and(Task::query()->whereKey($task)->count())->toBe(1);
});
