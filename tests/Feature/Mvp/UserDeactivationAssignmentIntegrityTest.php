<?php

use App\Models\Activity;
use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Identity\Presentation\Livewire\Users\Show as UserShow;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('requeues open customer assignments when an admin deactivates the customer', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create([
        'name' => 'Active',
        'last_name' => 'Customer',
        'email' => 'active-customer@example.test',
    ]);
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Assigned before deactivation',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(UserShow::class, ['user' => $customer->id])
        ->set('is_active', false)
        ->call('saveProfile')
        ->assertHasNoErrors();

    $task->refresh();

    expect($customer->refresh()->is_active)->toBeFalse()
        ->and($task->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($task->assigned_to)->toBeNull()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.status_changed')->exists())->toBeTrue()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'task.assignee_changed')->exists())->toBeTrue();
});
