<?php

use App\Models\Activity;
use App\Support\ActivityRecorder;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

it('hides moderation activity from customers but keeps it visible to admins on task detail', function (): void {
    [$admin, $customer, $project, $task] = customerActivityFixture();
    recordActivity($admin, $project, $task);

    $this->actingAs($admin)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('comment.hidden')
        ->assertSee('task.priority_changed');

    $this->actingAs($customer)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertDontSee('comment.hidden')
        ->assertSee('task.priority_changed');
});

it('hides moderation activity from customers but keeps it visible to admins on project detail', function (): void {
    [$admin, $customer, $project, $task] = customerActivityFixture();
    recordActivity($admin, $project, $task);

    $this->actingAs($admin)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('attachment.hidden')
        ->assertSee('project.status_changed');

    $this->actingAs($customer)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertDontSee('attachment.hidden')
        ->assertSee('project.status_changed');
});

it('hides moderation activity from customers but keeps it visible to admins on the dashboard', function (): void {
    [$admin, $customer, $project, $task] = customerActivityFixture();
    recordActivity($admin, $project, $task);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('comment.hidden')
        ->assertSee('task.priority_changed');

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('comment.hidden')
        ->assertSee('task.priority_changed');

    expect(Activity::query()->where('action', 'comment.hidden')->exists())->toBeTrue();
});

/**
 * @return array{User, User, Project, Task}
 */
function customerActivityFixture(): array
{
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Activity visibility project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Activity visibility task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    return [$admin, $customer, $project, $task];
}

function recordActivity(User $admin, Project $project, Task $task): void
{
    $recorder = app(ActivityRecorder::class);
    $recorder->record($admin, 'comment.hidden', $project, $task);
    $recorder->record($admin, 'attachment.hidden', $project, $task);
    $recorder->record($admin, 'task.priority_changed', $project, $task);
    $recorder->record($admin, 'project.status_changed', $project);
}
