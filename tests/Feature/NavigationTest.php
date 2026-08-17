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
    $adminClientDetail = $this->actingAs($admin)->get(route('clients.show', $client))->assertSuccessful()->getContent();
    $customerDashboard = $this->actingAs($customer)->get(route('dashboard'))->assertOk()->getContent();
    $customerProjectDetail = $this->actingAs($customer)
        ->get(route('projects.show', $project))
        ->assertSuccessful();

    $this->actingAs($customer)->get(route('clients.index'))->assertForbidden();

    expect($adminClientIndex)
        ->toContain(route('clients.create'))
        ->toContain(route('clients.show', $client))
        ->toContain('data-client-list="rows"')
        ->toContain('data-client-table="comparison"')
        ->toContain('data-client-id="'.$client->id.'"')
        ->toContain('data-status="active"')
        ->toContain('data-count-users="1"')
        ->toContain('data-count-projects="1"')
        ->toMatch('/<a class="[^"]*block[^"]*min-h-11[^"]*" href="'.preg_quote(route('clients.show', $client), '/').'" wire:navigate>.*?'.$client->name.'/s')
        ->and($adminProjectIndex)
        ->toContain(route('projects.create'))
        ->toContain(route('projects.show', $project))
        ->toContain('data-project-list="rows"')
        ->toContain('data-project-table="comparison"')
        ->toContain('data-project-id="'.$project->id.'"')
        ->toContain('data-status="active"')
        ->toContain('data-count-members="1"')
        ->toContain('data-count-tasks="0"')
        ->toMatch('/<a href="'.preg_quote(route('projects.show', $project), '/').'" wire:navigate class="[^"]*block[^"]*min-h-11[^"]*">.*?Visible Project/s')
        ->and($customerProjectIndex)
        ->toContain(route('projects.show', $project))
        ->not->toContain(route('projects.create'))
        ->and($adminClientDetail)
        ->toMatch('/<a class="group flex min-h-11[^>]*transition-colors[^>]*focus-visible:outline[^>]*" href="'.preg_quote(route('users.show', $customer), '/').'" wire:navigate>/')
        ->toMatch('/<a class="group flex min-h-11[^>]*transition-colors[^>]*focus-visible:outline[^>]*" href="'.preg_quote(route('projects.show', $project), '/').'" wire:navigate>/')
        ->and($customerProjectDetail->getContent())
        ->toContain($project->name)
        ->toMatch('/<a class="ui-loading-stable inline-flex min-h-11[^>]*transition-colors[^>]*focus-visible:outline[^>]*" href="'.preg_quote(route('projects.index'), '/').'" wire:navigate>/')
        ->and($customerDashboard)
        ->not->toContain(route('clients.index'))
        ->not->toContain(route('clients.create'));
});

it('renders precise empty-state actions for client and project indexes', function (): void {
    $admin = User::query()->admins()->firstOrFail();

    $clientIndex = $this->actingAs($admin)
        ->get(route('clients.index', ['q' => 'missing-client']))
        ->assertSuccessful()
        ->getContent();
    $projectIndex = $this->actingAs($admin)
        ->get(route('projects.index', ['q' => 'missing-project']))
        ->assertSuccessful()
        ->getContent();

    expect($clientIndex)
        ->toMatch('/<div data-empty-state="clients"[^>]*>.*?<a[^>]*href="'.preg_quote(route('clients.create'), '/').'"[^>]*wire:navigate[^>]*>اولین مشتری را بسازید\.<\/a>/s')
        ->and($projectIndex)
        ->toMatch('/<div data-empty-state="projects"[^>]*>.*?<a[^>]*href="'.preg_quote(route('projects.create'), '/').'"[^>]*wire:navigate[^>]*>پروژه جدید بسازید\.<\/a>/s');
});
