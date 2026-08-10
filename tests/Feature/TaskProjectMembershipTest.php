<?php

use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Application\Queries\TaskAccessScope;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Infrastructure\Models\Task;

it('only shows tasks from projects the user belongs to', function (): void {
    $member = User::factory()->create();
    $outsider = User::factory()->create();

    $memberProject = Project::query()->create(['title' => 'Member project']);
    $otherProject = Project::query()->create(['title' => 'Other project']);

    $memberProject->members()->attach($member->id);
    $otherProject->members()->attach($outsider->id);

    $visibleTask = Task::query()->create([
        'project_id' => $memberProject->id,
        'title' => 'Visible task',
        'is_done' => false,
    ]);
    $hiddenTask = Task::query()->create([
        'project_id' => $otherProject->id,
        'title' => 'Hidden task',
        'is_done' => false,
    ]);

    $scope = app(TaskAccessScope::class)->for($member);
    $tasks = app(TaskRepository::class)->search($scope);
    $ids = collect($tasks)->pluck('id')->all();

    expect($ids)->toContain($visibleTask->id)
        ->not->toContain($hiddenTask->id);
});

it('lets admin see tasks from every project', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $project = Project::query()->create(['title' => 'Admin project']);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'title' => 'Admin visible task',
        'is_done' => false,
    ]);

    $tasks = app(TaskRepository::class)->search(app(TaskAccessScope::class)->for($admin));

    expect(collect($tasks)->pluck('id'))->toContain($task->id);
});
