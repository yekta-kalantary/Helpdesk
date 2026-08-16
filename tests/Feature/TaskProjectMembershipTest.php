<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('only shows tasks from projects with active membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $member = User::factory()->customer($client)->create();
    $otherMember = User::factory()->customer($client)->create();

    $memberProject = mvpProject($client, 'Member project');
    $otherProject = mvpProject($client, 'Other project');
    $manager = app(ProjectMembershipManager::class);
    $manager->add($memberProject, $member, $admin);
    $manager->add($otherProject, $otherMember, $admin);

    app(TaskWorkflow::class)->createForAdmin($admin, $memberProject, [
        'title' => 'Visible task',
        'priority' => TaskPriority::Normal,
    ]);
    app(TaskWorkflow::class)->createForAdmin($admin, $otherProject, [
        'title' => 'Hidden task',
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($member)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Visible task')
        ->assertDontSee('Hidden task');
});

it('immediately removes task access when membership is removed', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Membership protected',
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($customer)->get(route('tasks.show', $task))->assertOk();

    $manager->remove($project, $customer, $admin);

    $this->get(route('tasks.show', $task))->assertNotFound();
});

it('lets admin see tasks from every project without membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $project = mvpProject($client, 'Admin project');

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Admin visible task',
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($admin)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Admin visible task');
});
