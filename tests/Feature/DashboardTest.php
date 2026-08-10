<?php

use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Infrastructure\Models\Task;

it('redirects authenticated users from home to dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

it('shows the system overview to admin', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $project = Project::query()->create([
        'title' => 'Admin dashboard project',
        'description' => null,
    ]);

    Task::query()->create([
        'project_id' => $project->id,
        'title' => 'Admin dashboard task',
        'description' => null,
        'is_done' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('داشبورد')
        ->assertSee('کاربران')
        ->assertSee('Admin dashboard project')
        ->assertSee('Admin dashboard task');
});

it('limits dashboard projects and tasks to normal user memberships', function (): void {
    $user = User::factory()->create();

    $visibleProject = Project::query()->create([
        'title' => 'Visible dashboard project',
        'description' => null,
    ]);
    $visibleProject->members()->attach($user->id);

    $hiddenProject = Project::query()->create([
        'title' => 'Hidden dashboard project',
        'description' => null,
    ]);

    Task::query()->create([
        'project_id' => $visibleProject->id,
        'title' => 'Visible dashboard task',
        'description' => null,
        'is_done' => false,
    ]);

    Task::query()->create([
        'project_id' => $hiddenProject->id,
        'title' => 'Hidden dashboard task',
        'description' => null,
        'is_done' => true,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('پروژه‌های من')
        ->assertSee('Visible dashboard project')
        ->assertSee('Visible dashboard task')
        ->assertDontSee('Hidden dashboard project')
        ->assertDontSee('Hidden dashboard task');
});
