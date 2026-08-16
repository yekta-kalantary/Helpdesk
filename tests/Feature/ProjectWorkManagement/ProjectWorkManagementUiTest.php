<?php

use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Projects\Infrastructure\Models\WorkGroup;
use Modules\Projects\Presentation\Livewire\Show as ProjectShow;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('renders Kanban and hierarchy from project-owned workflow and searches independently of Work Groups', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'UI project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $group = app(WorkGroupManager::class)->create($admin, $project, [
        'title' => 'Delivery group',
        'description' => 'Grouped delivery work',
    ]);
    $workflow = app(TaskWorkflow::class);
    $workflow->createForAdmin($admin, $project, [
        'title' => 'Root searchable task',
        'priority' => TaskPriority::Normal,
    ]);
    $workflow->createForAdmin($admin, $project, [
        'title' => 'Grouped needle task',
        'work_group_id' => $group->id,
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($customer);

    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('کانبان پروژه')
        ->assertSee('Root Tasks')
        ->assertSee('Delivery group')
        ->assertSee('Root searchable task')
        ->assertSee('Grouped needle task')
        ->set('taskSearch', 'Grouped needle')
        ->assertDontSee('Root searchable task')
        ->assertSee('Grouped needle task');
});

it('lets a project member move any visible task through Kanban regardless of assignment', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $assignee = User::factory()->customer($client)->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $assignee, $admin);
    $memberships->add($project, $member, $admin);
    $target = mvpOpenStatus($project, 1);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Kanban shared move',
        'priority' => TaskPriority::Normal,
        'assigned_to' => $assignee->id,
    ]);

    $this->actingAs($member);

    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->call('moveTask', $task->id, $target->id)
        ->assertHasNoErrors();

    expect($task->refresh()->project_status_id)->toBe($target->id)
        ->and($task->assigned_to)->toBe($assignee->id);
});

it('exposes workflow and Work Group management only to admins and persists Work Group descriptions', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $this->actingAs($admin);
    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->set('newStatusTitle', 'بازبینی UI')
        ->call('createStatus')
        ->assertHasNoErrors()
        ->set('newWorkGroupTitle', 'UI group')
        ->set('newWorkGroupDescription', 'UI group description')
        ->call('createWorkGroup')
        ->assertHasNoErrors();

    expect($project->taskStatuses()->where('title', 'بازبینی UI')->exists())->toBeTrue()
        ->and(WorkGroup::query()->where('project_id', $project->id)->where('title', 'UI group')->value('description'))->toBe('UI group description');

    $this->actingAs($customer);
    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertDontSee('Workflow پروژه')
        ->assertDontSee('مدیریت Work Group')
        ->call('createStatus')
        ->assertForbidden();
});
