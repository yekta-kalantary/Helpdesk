<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('shows customers only dashboard data from active project memberships', function (): void {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customerA = User::factory()->customer($clientA)->create();
    $customerB = User::factory()->customer($clientB)->create();
    $projectA = mvpProject($clientA, 'Visible Project A');
    $projectB = mvpProject($clientB, 'Hidden Project B');
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($projectA, $customerA, $admin);
    $memberships->add($projectB, $customerB, $admin);

    app(TaskWorkflow::class)->createForAdmin($admin, $projectA, [
        'title' => 'Visible Task A',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customerA->id,
        'due_date' => today()->subDay()->toDateString(),
    ]);
    app(TaskWorkflow::class)->createForAdmin($admin, $projectB, [
        'title' => 'Hidden Task B',
        'status' => TaskStatus::WaitingCustomer,
        'priority' => TaskPriority::High,
        'assigned_to' => $customerB->id,
    ]);

    $this->actingAs($customerA)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Visible Project A')
        ->assertSee('Visible Task A')
        ->assertDontSee('Hidden Project B')
        ->assertDontSee('Hidden Task B');
});

it('drops project and task data from the customer dashboard immediately after membership removal', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Membership Project');
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($project, $customer, $admin);

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Membership Task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Membership Project')
        ->assertSee('Membership Task');

    $memberships->remove($project, $customer, $admin);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Membership Project')
        ->assertDontSee('Membership Task');
});
