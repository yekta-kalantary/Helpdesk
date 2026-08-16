<?php

use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('rejects project completion while any task is in an open project status', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Open task',
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => app(ProjectLifecycle::class)->complete($project, $admin))
        ->toThrow(DomainException::class);
});

it('allows completion only when every task is in the current Done status and lets admin reopen', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $done = mvpDoneStatus($project);

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Done one',
        'project_status_id' => $done->id,
        'priority' => TaskPriority::Normal,
    ]);
    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Done two',
        'project_status_id' => $done->id,
        'priority' => TaskPriority::Normal,
    ]);

    app(ProjectLifecycle::class)->complete($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Completed);

    app(ProjectLifecycle::class)->reopen($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Active)
        ->and(Activity::query()->where('project_id', $project->id)->where('action', 'project.status_changed')->count())->toBe(2);
});

it('locks the project before checking concurrent task state', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    app(ProjectLifecycle::class)->complete($project, $admin);

    expect(collect($queries)->contains(fn (string $query): bool => str_contains($query, 'for update')))->toBeTrue();
});
