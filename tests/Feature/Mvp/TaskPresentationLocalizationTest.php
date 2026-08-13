<?php

use App\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('renders Persian task labels and terminal unassigned presentation without raw keys', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $project = mvpProject($client);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Localized terminal task',
        'status' => TaskStatus::Completed,
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
        ->assertSee('تکمیل‌شده')
        ->assertSee('زیاد')
        ->assertSee('بدون مسئول')
        ->assertSee('تکمیل شد')
        ->assertDontSee('completed')
        ->assertDontSee('>high<')
        ->assertDontSee('task.completed');
});

it('renders the admin queue label for an unassigned waiting admin task', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $project = mvpProject($client);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Localized admin queue task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('صف ادمین')
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
