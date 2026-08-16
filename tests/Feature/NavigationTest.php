<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

it('renders role-aware authenticated navigation', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer(Client::factory()->create())->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('users.index'))
        ->assertSee(route('clients.index'))
        ->assertSee(route('projects.index'))
        ->assertSee(route('tasks.index'))
        ->assertSee(route('notifications.index'));

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('users.index'))
        ->assertDontSee(route('clients.index'))
        ->assertSee(route('projects.index'))
        ->assertSee(route('tasks.index'))
        ->assertSee(route('notifications.index'));
});
