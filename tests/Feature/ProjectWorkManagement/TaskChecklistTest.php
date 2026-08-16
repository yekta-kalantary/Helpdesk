<?php

use App\Models\Activity;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Tasks\Application\TaskChecklist;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\TaskChecklistItem;

it('lets project members manage one level checklist without independent task semantics', function (): void {
    Notification::fake();
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, ['title' => 'Parent', 'priority' => TaskPriority::Normal]);
    Notification::fake();
    $checklist = app(TaskChecklist::class);

    $item = $checklist->add($customer, $task, 'First step');
    $item = $checklist->rename($admin, $item, 'Renamed step');
    $item = $checklist->toggle($customer, $item, true);

    expect($item->title)->toBe('Renamed step')
        ->and($item->is_completed)->toBeTrue()
        ->and(Schema::hasColumn('task_checklist_items', 'parent_id'))->toBeFalse()
        ->and(Schema::hasColumn('task_checklist_items', 'assigned_to'))->toBeFalse()
        ->and(Schema::hasColumn('task_checklist_items', 'project_status_id'))->toBeFalse();

    Notification::assertNothingSent();
});

it('keeps task and checklist completion independent and preserves checklist state across task reopen', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $workflow = app(TaskWorkflow::class);
    $checklist = app(TaskChecklist::class);
    $open = $project->taskStatuses()->active()->where('is_done', false)->firstOrFail();
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();
    $task = $workflow->createForAdmin($admin, $project, ['title' => 'Independent', 'priority' => TaskPriority::Normal]);
    $checked = $checklist->toggle($admin, $checklist->add($admin, $task, 'Checked'), true);
    $openItem = $checklist->add($admin, $task, 'Still open');

    expect($task->refresh()->isDone())->toBeFalse();

    $task = $workflow->changeStatus($admin, $task, $done);
    expect($checked->refresh()->is_completed)->toBeTrue()
        ->and($openItem->refresh()->is_completed)->toBeFalse();

    $task = $workflow->changeStatus($admin, $task, $open);
    expect($checked->refresh()->is_completed)->toBeTrue()
        ->and($openItem->refresh()->is_completed)->toBeFalse();
});

it('makes checklist read only on done tasks and completed projects', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $workflow = app(TaskWorkflow::class);
    $checklist = app(TaskChecklist::class);
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();
    $task = $workflow->createForAdmin($admin, $project, ['title' => 'Readonly', 'priority' => TaskPriority::Normal]);
    $item = $checklist->add($admin, $task, 'Before done');
    $task = $workflow->changeStatus($admin, $task, $done);

    expect(fn () => $checklist->toggle($admin, $item, true))->toThrow(DomainException::class);

    app(ProjectLifecycle::class)->complete($project, $admin);
    expect(fn () => $checklist->add($admin, $task, 'Nope'))->toThrow(DomainException::class);
});

it('logically removes checklist items and records parent task activity', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, ['title' => 'Audit parent', 'priority' => TaskPriority::Normal]);
    $checklist = app(TaskChecklist::class);
    $item = $checklist->add($admin, $task, 'Disposable');

    expect(fn () => $item->delete())->toThrow(DomainException::class);

    $checklist->remove($admin, $item);

    expect($item->refresh()->removed_at)->not->toBeNull()
        ->and(TaskChecklistItem::query()->whereKey($item)->exists())->toBeTrue()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'subtask.removed')->exists())->toBeTrue();
});


it('inherits task visibility and rejects checklist mutations from users without project access', function (): void {
    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $outsider = User::factory()->customer($otherClient)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, ['title' => 'Protected checklist', 'priority' => TaskPriority::Normal]);

    expect(fn () => app(TaskChecklist::class)->add($outsider, $task, 'Forbidden'))->toThrow(DomainException::class);
});

it('reorders every active checklist item stably and rejects incomplete order payloads', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, ['title' => 'Order parent', 'priority' => TaskPriority::Normal]);
    $checklist = app(TaskChecklist::class);
    $one = $checklist->add($admin, $task, 'One');
    $two = $checklist->add($admin, $task, 'Two');
    $three = $checklist->add($admin, $task, 'Three');

    expect(fn () => $checklist->reorder($admin, $task, [$one->id, $two->id]))->toThrow(DomainException::class);

    $checklist->reorder($admin, $task, [$three->id, $one->id, $two->id]);

    expect($task->checklistItems()->pluck('id')->all())->toBe([$three->id, $one->id, $two->id]);
});

it('preserves checklist state across task status and Work Group moves and emits only parent-task audit events', function (): void {
    Notification::fake();
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $group = app(WorkGroupManager::class)->create($admin, $project, ['title' => 'Delivery']);
    $workflow = app(TaskWorkflow::class);
    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Movable parent',
        'work_group_id' => $group->id,
        'priority' => TaskPriority::Normal,
    ]);
    $checklist = app(TaskChecklist::class);
    $item = $checklist->add($admin, $task, 'Persistent step');
    Notification::fake();

    $item = $checklist->toggle($admin, $item, true);
    $item = $checklist->toggle($admin, $item, false);
    $item = $checklist->rename($admin, $item, 'Persistent renamed step');
    $task = $workflow->changeStatus($admin, $task, mvpOpenStatus($project, 1));
    $task = $workflow->updateByAdmin($admin, $task, ['work_group_id' => null]);

    expect($item->refresh()->title)->toBe('Persistent renamed step')
        ->and($item->is_completed)->toBeFalse()
        ->and($task->checklistItems()->whereKey($item)->exists())->toBeTrue()
        ->and($task->work_group_id)->toBeNull()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'subtask.added')->exists())->toBeTrue()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'subtask.completed')->exists())->toBeTrue()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'subtask.uncompleted')->exists())->toBeTrue()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'subtask.renamed')->exists())->toBeTrue();

    Notification::assertNothingSent();
});
