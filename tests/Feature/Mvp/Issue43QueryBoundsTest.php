<?php

use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\TaskComment;

it('bounds project detail collections while keeping their pagination reachable and ordered', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Bounded project');

    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    foreach (range(1, 21) as $number) {
        $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
            'title' => "Project task {$number}",
            'status' => TaskStatus::WaitingAdmin,
            'priority' => TaskPriority::Normal,
        ]);
        DB::table('tasks')->where('id', $task->id)->update([
            'updated_at' => now()->subMinutes($number),
        ]);

        Activity::query()->create([
            'project_id' => $project->id,
            'task_id' => $task->id,
            'action' => sprintf('Project activity %02d', $number),
            'created_at' => now()->subMinutes($number),
        ]);
    }

    $response = $this->actingAs($customer)->get(route('projects.show', $project));

    $response->assertOk()
        ->assertSee('Project task 1')
        ->assertDontSee('Project task 21')
        ->assertSee('Project activity 21')
        ->assertDontSee('Project activity 01')
        ->assertSee('tasksPage=2')
        ->assertSee('projectActivitiesPage=2');

    $this->actingAs($customer)
        ->get(route('projects.show', $project).'?tasksPage=2&projectActivitiesPage=2')
        ->assertOk()
        ->assertSee('Project task 21')
        ->assertSee('Project activity 01')
        ->assertDontSee('Project task 1')
        ->assertDontSee('Project activity 21');
});

it('bounds task detail collections without exposing another tenant', function (): void {
    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $otherCustomer = User::factory()->customer($otherClient)->create();
    $project = mvpProject($client, 'Bounded task project');
    $otherProject = mvpProject($otherClient, 'Other tenant project');

    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    app(ProjectMembershipManager::class)->add($otherProject, $otherCustomer, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Bounded task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $otherTask = app(TaskWorkflow::class)->createForAdmin($admin, $otherProject, [
        'title' => 'Other tenant task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    foreach (range(1, 21) as $number) {
        TaskComment::query()->create([
            'task_id' => $task->id,
            'user_id' => $customer->id,
            'body' => "Task comment {$number}",
        ]);

        Activity::query()->create([
            'task_id' => $task->id,
            'project_id' => $project->id,
            'action' => sprintf('Task activity %02d', $number),
            'created_at' => now()->subMinutes($number),
        ]);
    }

    $response = $this->actingAs($customer)->get(route('tasks.show', $task));

    $response->assertOk()
        ->assertSee('Task comment 1')
        ->assertDontSee('Task comment 21')
        ->assertSee('Task activity 21')
        ->assertDontSee('Task activity 01')
        ->assertSee('commentsPage=2')
        ->assertSee('taskActivitiesPage=2');

    $this->actingAs($customer)
        ->get(route('tasks.show', $task).'?commentsPage=2&taskActivitiesPage=2')
        ->assertOk()
        ->assertSee('Task comment 21')
        ->assertSee('Task activity 01')
        ->assertDontSee('Task comment 1')
        ->assertDontSee('Task activity 21');

    $this->get(route('tasks.show', $otherTask))->assertNotFound();
});

it('scopes dashboard activities without materializing all visible project ids', function (): void {
    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $otherCustomer = User::factory()->customer($otherClient)->create();
    $project = mvpProject($client, 'Dashboard project');
    $otherProject = mvpProject($otherClient, 'Other dashboard project');

    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    app(ProjectMembershipManager::class)->add($otherProject, $otherCustomer, $admin);

    Activity::query()->create([
        'project_id' => $project->id,
        'action' => 'Visible dashboard activity',
        'created_at' => now(),
    ]);
    Activity::query()->create([
        'project_id' => $otherProject->id,
        'action' => 'Hidden dashboard activity',
        'created_at' => now()->subMinute(),
    ]);

    DB::enableQueryLog();
    $response = $this->actingAs($customer)->get(route('dashboard'));
    $queries = DB::getQueryLog();

    $response->assertOk()
        ->assertSee('Visible dashboard activity')
        ->assertDontSee('Hidden dashboard activity');

    expect(collect($queries)->pluck('query')->implode(' '))->not->toContain('select `id` from `projects`');
});
