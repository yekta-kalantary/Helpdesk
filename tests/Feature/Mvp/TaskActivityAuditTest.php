<?php

use App\Models\Activity;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('audits project status changes with immutable id and title snapshots without touching assignee', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $assignee = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $member, $admin);
    $memberships->add($project, $assignee, $admin);
    $openA = mvpOpenStatus($project);
    $openB = mvpOpenStatus($project, 1);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Audit status movement',
        'project_status_id' => $openA->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    $beforeAssigneeActivity = Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->count();
    $task = app(TaskWorkflow::class)->changeStatus($member, $task, $openB);

    $status = Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->latest('id')->firstOrFail();

    expect($task->assigned_to)->toBe($assignee->id)
        ->and($status->actor_id)->toBe($member->id)
        ->and($status->project_id)->toBe($project->id)
        ->and($status->metadata)->toMatchArray([
            'previous_status_id' => $openA->id,
            'previous_status_title_snapshot' => $openA->title,
            'new_status_id' => $openB->id,
            'new_status_title_snapshot' => $openB->title,
        ])
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->count())->toBe($beforeAssigneeActivity);
});

it('records completion exactly once when a task enters the current Done status', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $workflow = app(TaskWorkflow::class);
    $open = mvpOpenStatus($project);
    $done = mvpDoneStatus($project);
    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Complete audit',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
    ]);

    $task = $workflow->changeStatus($admin, $task, $done);
    $task = $workflow->changeStatus($admin, $task, $done);

    expect(Activity::query()->where('task_id', $task->id)->where('action', 'task.completed')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->count())->toBe(1)
        ->and($task->completed_at)->not->toBeNull();
});

it('records reopened exactly once when a Done task moves to an Open status', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $workflow = app(TaskWorkflow::class);
    $open = mvpOpenStatus($project);
    $done = mvpDoneStatus($project);
    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Reopen audit',
        'project_status_id' => $done->id,
        'priority' => TaskPriority::Normal,
    ]);

    $task = $workflow->changeStatus($admin, $task, $open);
    $task = $workflow->changeStatus($admin, $task, $open);

    expect(Activity::query()->where('task_id', $task->id)->where('action', 'task.reopened')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->count())->toBe(1)
        ->and($task->completed_at)->toBeNull();
});

it('does not record completion or reopen events for open to open movement', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $workflow = app(TaskWorkflow::class);
    $openA = mvpOpenStatus($project);
    $openB = mvpOpenStatus($project, 1);
    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Open movement audit',
        'project_status_id' => $openA->id,
        'priority' => TaskPriority::Normal,
    ]);

    $workflow->changeStatus($admin, $task, $openB);

    expect(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->count())->toBe(1)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.completed')->count())->toBe(0)
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.reopened')->count())->toBe(0);
});
