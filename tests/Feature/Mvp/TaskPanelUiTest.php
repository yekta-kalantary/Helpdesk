<?php

use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Presentation\Livewire\Form;
use Modules\Tasks\Presentation\Livewire\Index;

it('keeps task list filters and responsive task fields represented', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $project = mvpProject($client, 'Panel project');
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Panel task',
        'priority' => TaskPriority::High,
    ]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertSee('Panel task')
        ->assertSeeHtml('wire:model.live.debounce.300ms="q"')
        ->assertSeeHtml('wire:model.live="project"')
        ->assertSeeHtml('wire:model.live="status"')
        ->assertSeeHtml('wire:model.live="priority"')
        ->assertSeeHtml('wire:model.live="assignee"')
        ->assertSeeHtml('wire:model.live="overdue"')
        ->assertSeeHtml('wire:model.live="sort"')
        ->assertSee($task->reference)
        ->assertSee('Panel project');
});

it('loads project task statuses in the task create form', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $project = mvpProject($client);
    $status = mvpOpenStatus($project, 1);

    $this->actingAs($admin);

    Livewire::test(Form::class)
        ->set('project_id', $project->id)
        ->assertSee($status->title)
        ->assertSeeHtml('wire:model="project_status_id"');
});

it('hides admin-only task create controls from customers', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, User::query()->admins()->firstOrFail());

    $this->actingAs($customer);

    Livewire::test(Form::class)
        ->set('project_id', $project->id)
        ->assertDontSeeHtml('name="priority"')
        ->assertDontSeeHtml('name="assigned_to"')
        ->assertDontSeeHtml('name="due_date"')
        ->assertSee('تسک مشتری در ریشه پروژه ایجاد می‌شود');
});
