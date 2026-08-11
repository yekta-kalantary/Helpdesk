<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('redirects authenticated users from home to dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

it('shows the system overview to admin', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create(['name' => 'Admin dashboard client']);
    $project = mvpProject($client, 'Admin dashboard project');

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Admin dashboard task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('داشبورد')
        ->assertSee('مشتریان فعال')
        ->assertSee('صف ادمین')
        ->assertSee('Admin dashboard project')
        ->assertSee('Admin dashboard task');
});

it('limits customer dashboard projects and tasks to active memberships', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();

    $visibleProject = mvpProject($client, 'Visible dashboard project');
    $hiddenProject = mvpProject($client, 'Hidden dashboard project');
    app(ProjectMembershipManager::class)->add($visibleProject, $customer, $admin);

    app(TaskWorkflow::class)->createForAdmin($admin, $visibleProject, [
        'title' => 'Visible dashboard task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    app(TaskWorkflow::class)->createForAdmin($admin, $hiddenProject, [
        'title' => 'Hidden dashboard task',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('پروژه‌های فعال من')
        ->assertSee('Visible dashboard project')
        ->assertSee('Visible dashboard task')
        ->assertDontSee('Hidden dashboard project')
        ->assertDontSee('Hidden dashboard task');
});
