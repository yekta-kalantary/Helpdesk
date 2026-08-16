<?php

use App\Models\Activity;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\ProjectLifecycle;
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
