<?php

use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Projects\Domain\Enums\ProjectStatus;
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
        ->assertSeeHtml('aria-label="Section navigation"')
        ->assertSeeHtml('aria-current="location"')
        ->assertSeeHtml('aria-label="برد کانبان با پیمایش افقی"')
        ->assertSeeHtml('wire:change="moveTask(')
        ->assertSeeHtml('class="min-h-11 w-full rounded-lg border-workspace-border text-xs"')
        ->assertSee('Root Tasks')
        ->assertSee('Delivery group')
        ->assertSee('Root searchable task')
        ->assertSee('Grouped needle task')
        ->set('taskSearch', 'Grouped needle')
        ->assertDontSee('Root searchable task')
        ->assertSee('Grouped needle task');
});

it('renders project workspace sections for admins and keeps management controls private', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Workspace project');
    $project->forceFill([
        'start_date' => '2026-08-01',
        'due_date' => '2026-08-31',
    ])->save();
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $this->actingAs($admin);

    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('Kanban')
        ->assertSee('Tasks')
        ->assertSee('Activity')
        ->assertSee('Members')
        ->assertSee('Project Management')
        ->assertSeeInOrder([
            'data-project-header',
            'فهرست تسک‌ها',
            '</header>',
            'data-project-management-disclosure',
        ])
        ->assertSeeHtml('<details data-project-management-disclosure')
        ->assertDontSeeHtml('data-project-management-disclosure open')
        ->assertSeeInOrder([
            '<details class="group rounded-workspace border border-workspace-divider bg-workspace-surface">',
            'جزئیات پروژه',
            'تعداد اعضا: 1',
            'شروع: 2026/08/01',
            'موعد: 2026/08/31',
            '</details>',
        ])
        ->assertDontSeeHtml('<details class="group rounded-workspace border border-workspace-divider bg-workspace-surface" open')
        ->assertSeeInOrder([
            'data-project-management-disclosure',
            'wire:click="complete"',
            'Workflow پروژه',
            'wire:submit="createStatus"',
            'مدیریت Work Group',
            'wire:submit="createWorkGroup"',
        ])
        ->assertSee($client->name)
        ->assertSee('Workflow پروژه')
        ->assertSee('مدیریت Work Group');

    $this->actingAs($customer);

    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('Kanban')
        ->assertSee('Tasks')
        ->assertSee('Activity')
        ->assertSee('Members')
        ->assertDontSee($client->name)
        ->assertDontSee('Project Management')
        ->assertDontSee('Workflow پروژه')
        ->assertDontSee('مدیریت Work Group')
        ->assertDontSeeHtml('data-project-management-disclosure')
        ->assertDontSeeHtml('wire:click="complete"');
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

it('explains that completed project boards are read-only to customers', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Completed workspace');
    $project->forceFill(['status' => ProjectStatus::Completed])->save();
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $this->actingAs($customer);

    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('این پروژه تکمیل شده و برد فقط خواندنی است.')
        ->assertSee('برای تغییر وضعیت تسک یا ایجاد تسک جدید، ابتدا پروژه را بازگشایی کنید.')
        ->assertSeeHtml('aria-label="برد کانبان با پیمایش افقی"')
        ->assertDontSeeHtml('wire:change="moveTask(')
        ->assertDontSeeHtml('draggable="true"');
});
