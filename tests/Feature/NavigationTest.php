<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

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
        ->not->toContain(route('users.index'))
        ->not->toContain(route('clients.index'))
        ->toContain(route('projects.index'))
        ->toContain(route('tasks.index'))
        ->toContain(route('notifications.index'));
});
