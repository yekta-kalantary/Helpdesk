<?php

use Modules\Identity\Infrastructure\Models\User;

it('shares the application shell contract with guests', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('appName', config('app.name'))
            ->where('locale', config('app.locale'))
            ->where('direction', config('app.locale') === 'fa' ? 'rtl' : 'ltr')
            ->where('auth.user', null)
            ->where('auth.capabilities', [])
            ->where('navigation', [
                ['label' => __('navigation.dashboard'), 'href' => '/'],
                ['label' => __('navigation.users'), 'href' => '/users', 'capability' => 'users.view'],
                ['label' => __('navigation.clients'), 'href' => '/clients', 'capability' => 'clients.view'],
                ['label' => __('navigation.projects'), 'href' => '/projects', 'capability' => 'projects.view'],
                ['label' => __('navigation.tasks'), 'href' => '/tasks', 'capability' => 'tasks.view'],
            ]));
});

it('shares authenticated user presentation data and capabilities', function (): void {
    $user = User::factory()->admin()->create([
        'name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
    ]);

    $this->actingAs($user)->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('auth.user', [
                'id' => $user->id,
                'name' => 'Ada Lovelace',
                'email' => 'ada@example.test',
            ])
            ->where('auth.capabilities', [
                'users.view',
                'clients.view',
                'projects.view',
                'tasks.view',
            ]));
});
