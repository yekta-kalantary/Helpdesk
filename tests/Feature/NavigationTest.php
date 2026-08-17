<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;

it('renders role-aware authenticated navigation', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer(Client::factory()->create())->create();

    $adminResponse = $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk();

    preg_match('/<nav[^>]*aria-label="ناوبری اصلی"[^>]*>(.*?)<\/nav>/s', $adminResponse->getContent(), $adminNavigationMatches);
    $adminNavigation = $adminNavigationMatches[1] ?? '';

    expect($adminNavigation)->not->toBeEmpty()
        ->toContain(route('users.index'))
        ->toContain(route('clients.index'))
        ->toContain(route('projects.index'))
        ->toContain(route('tasks.index'))
        ->toContain(route('notifications.index'));

    $customerResponse = $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk();

    preg_match('/<nav[^>]*aria-label="ناوبری اصلی"[^>]*>(.*?)<\/nav>/s', $customerResponse->getContent(), $customerNavigationMatches);
    $customerNavigation = $customerNavigationMatches[1] ?? '';

    expect($customerNavigation)->not->toBeEmpty()
        ->toContain('صفحه اصلی')
        ->toContain('کارها')
        ->toContain('فضاها')
        ->not->toContain(route('users.index'))
        ->not->toContain(route('clients.index'))
        ->toContain(route('projects.index'))
        ->toContain(route('tasks.index'))
        ->toContain(route('notifications.index'));

    expect($adminResponse->getContent())
        ->toContain('aria-label="ناوبری اصلی"')
        ->toContain('data-sidebar')
        ->toContain('aria-hidden="true"')
        ->toContain('id="main-content"')
        ->toContain('data-route-focus')
        ->toContain('data-sidebar-open')
        ->toContain('data-sidebar-close')
        ->toContain('data-sidebar-backdrop');
});

it('keeps client and project entry actions role-aware and links visible projects to details', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create(['name' => 'Acme Client']);
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Visible Project');

    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $adminClientIndex = $this->actingAs($admin)->get(route('clients.index'))->assertOk()->getContent();
    $adminProjectIndex = $this->actingAs($admin)->get(route('projects.index'))->assertOk()->getContent();
    $customerProjectIndex = $this->actingAs($customer)->get(route('projects.index'))->assertOk()->getContent();
    $customerDashboard = $this->actingAs($customer)->get(route('dashboard'))->assertOk()->getContent();
    $customerProjectDetail = $this->actingAs($customer)
        ->get(route('projects.show', $project))
        ->assertSuccessful();

    $this->actingAs($customer)->get(route('clients.index'))->assertForbidden();

    expect($adminClientIndex)
        ->toContain(route('clients.create'))
        ->toContain(route('clients.show', $client))
        ->and($adminProjectIndex)
        ->toContain(route('projects.create'))
        ->toContain(route('projects.show', $project))
        ->and($customerProjectIndex)
        ->toContain(route('projects.show', $project))
        ->not->toContain(route('projects.create'))
        ->and($customerProjectDetail->getContent())
        ->toContain($project->name)
        ->and($customerDashboard)
        ->not->toContain(route('clients.index'))
        ->not->toContain(route('clients.create'));
});
