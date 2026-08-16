<?php

use App\Notifications\ResourceChangedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Presentation\Livewire\Index;
use Modules\Tasks\Presentation\Livewire\Show;

it('generates canonical task URLs from immutable references', function (): void {
    $task = taskForRouting();

    expect(route('tasks.show', $task))->toEndWith('/tasks/'.$task->reference)
        ->and(route('tasks.show', $task))->not->toEndWith('/tasks/'.$task->id);
});

it('redirects authorized numeric task URLs to the canonical reference URL', function (): void {
    $admin = User::factory()->admin()->create();
    $task = taskForRouting($admin);

    $this->actingAs($admin)
        ->get('/tasks/'.$task->id)
        ->assertRedirect(route('tasks.show', $task));
});

it('redirects authorized numeric task edit URLs to the canonical reference URL', function (): void {
    $admin = User::factory()->admin()->create();
    $task = taskForRouting($admin);

    $this->actingAs($admin)
        ->get('/tasks/'.$task->id.'/edit')
        ->assertRedirect(route('tasks.edit', $task));
});

it('does not disclose unauthorized tasks through numeric redirects', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->customer($client)->create();
    $viewer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $owner, $admin);

    $task = app(TaskWorkflow::class)->createForCustomer($owner, $project, ['title' => 'Private task']);

    $this->actingAs($viewer)->get('/tasks/'.$task->id)->assertNotFound();
});

it('mounts the task show component using its reference', function (): void {
    $admin = User::factory()->admin()->create();
    $task = taskForRouting($admin);

    Livewire::actingAs($admin)
        ->test(Show::class, ['task' => $task->reference])
        ->assertSet('taskId', $task->id);
});

it('searches tasks by their immutable reference', function (): void {
    $admin = User::factory()->admin()->create();
    $task = taskForRouting($admin);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('q', $task->reference)
        ->assertSee($task->reference);
});

it('uses canonical task URLs in notifications while keeping numeric resource ids', function (): void {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $assignee = User::factory()->admin()->create();
    $task = taskForRouting($admin);

    app(TaskWorkflow::class)->updateByAdmin($admin, $task, [
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    Notification::assertSentTo($assignee, ResourceChangedNotification::class, function (ResourceChangedNotification $notification) use ($task): bool {
        return $notification->url === route('tasks.show', $task)
            && $notification->payload['resource_id'] === $task->id
            && $notification->payload['reference'] === $task->reference;
    });
});

function taskForRouting(?User $admin = null): Task
{
    $admin ??= User::factory()->admin()->create();
    $task = app(TaskWorkflow::class)->createForAdmin($admin, mvpProject(Client::factory()->create()), [
        'title' => 'Routing task',
        'priority' => TaskPriority::Normal,
        'assigned_to' => null,
    ]);

    return $task;
}
