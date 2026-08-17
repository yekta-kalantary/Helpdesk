<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;

it('shows project dates to admins and project customers', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Dated project');
    $project->update([
        'start_date' => '2026-08-01',
        'due_date' => '2026-08-31',
    ]);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $this->actingAs($admin)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('2026/08/01')
        ->assertSee('2026/08/31');

    $this->actingAs($customer)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('2026/08/01')
        ->assertSee('2026/08/31');
});

it('shows a neutral placeholder for absent project dates to admins and customers', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Undated project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    foreach ([$admin, $customer] as $user) {
        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('شروع:')
            ->assertSee('موعد')
            ->assertSee('—');
    }
});
