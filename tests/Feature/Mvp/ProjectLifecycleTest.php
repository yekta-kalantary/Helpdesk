<?php

use App\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('rejects project completion while a non terminal task exists', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Open task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => app(ProjectLifecycle::class)->complete($project, $admin))
        ->toThrow(DomainException::class);
});

it('allows completion when every task is terminal and lets admin reopen', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Done',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
    ]);
    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Cancelled',
        'status' => TaskStatus::Cancelled,
        'priority' => TaskPriority::Normal,
    ]);

    app(ProjectLifecycle::class)->complete($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Completed);

    app(ProjectLifecycle::class)->reopen($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Active)
        ->and(Activity::query()->where('project_id', $project->id)->where('action', 'project.status_changed')->count())->toBe(2);
});
