<?php

use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Infrastructure\Models\Task;

it('only shows tasks from projects the user belongs to', function (): void {
    $member = User::factory()->create();
    $outsider = User::factory()->create();

    $memberProject = Project::query()->create(['title' => 'Member project']);
    $otherProject = Project::query()->create(['title' => 'Other project']);

    $memberProject->members()->attach($member->id);
    $otherProject->members()->attach($outsider->id);

    Task::query()->create([
        'project_id' => $memberProject->id,
        'title' => 'Visible task',
        'is_done' => false,
    ]);

    Task::query()->create([
        'project_id' => $otherProject->id,
        'title' => 'Hidden task',
        'is_done' => false,
    ]);

    $this->actingAs($member)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Visible task')
        ->assertDontSee('Hidden task');
});

it('lets admin see tasks from every project', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $project = Project::query()->create(['title' => 'Admin project']);

    Task::query()->create([
        'project_id' => $project->id,
        'title' => 'Admin visible task',
        'is_done' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Admin visible task');
});
