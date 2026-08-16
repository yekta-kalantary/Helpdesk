<?php

use App\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('renders project-owned status titles Persian priority and activity labels without raw keys', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $project = mvpProject($client);
    $done = mvpDoneStatus($project);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Localized done task',
        'project_status_id' => $done->id,
        'priority' => TaskPriority::High,
        'assigned_to' => null,
    ]);

    Activity::query()->create([
        'actor_id' => $admin->id,
        'project_id' => $project->id,
        'task_id' => $task->id,
        'action' => 'task.completed',
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee($done->title)
        ->assertSee('زیاد')
        ->assertSee('بدون مسئول')
        ->assertSee('تسک تکمیل شد')
        ->assertDontSee('task.completed');
});

it('renders an unassigned open task without legacy admin queue labels', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $project = mvpProject($client);
    $open = mvpOpenStatus($project);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Localized open task',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee($open->title)
        ->assertSee('بدون مسئول')
        ->assertDontSee('صف ادمین')
        ->assertDontSee('waiting_admin');
});

it('renders Persian role labels without the stored role key', function (): void {
    $customer = User::factory()->customer(Client::factory()->create())->create();

    $this->actingAs(User::query()->admins()->firstOrFail())
        ->get(route('users.show', $customer))
        ->assertOk()
        ->assertSee('مشتری')
        ->assertDontSee('Customer');
});
