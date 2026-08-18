<?php

use Modules\Identity\Infrastructure\Models\User;

it('shares the application shell contract with guests', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('appName', config('app.name'))
            ->where('locale', config('app.locale'))
            ->where('direction', config('app.locale') === 'fa' ? 'rtl' : 'ltr')
            ->where('navigationLabel', __('app.navigation.label'))
            ->where('auth.user', null)
            ->where('auth.capabilities', [])
            ->where('navigation', [
                [
                    'key' => 'overview',
                    'label' => __('app.navigation.overview'),
                    'items' => [
                        ['key' => 'dashboard', 'label' => __('navigation.dashboard'), 'href' => route('dashboard')],
                    ],
                ],
                [
                    'key' => 'workspace',
                    'label' => __('app.navigation.workspace'),
                    'items' => [
                        ['key' => 'clients', 'label' => __('navigation.clients'), 'href' => null, 'pending' => true, 'capability' => 'clients.view'],
                        ['key' => 'projects', 'label' => __('navigation.projects'), 'href' => null, 'pending' => true, 'capability' => 'projects.view'],
                        ['key' => 'tasks', 'label' => __('navigation.tasks'), 'href' => null, 'pending' => true, 'capability' => 'tasks.view'],
                    ],
                ],
                [
                    'key' => 'administration',
                    'label' => __('app.navigation.administration'),
                    'items' => [
                        ['key' => 'users', 'label' => __('navigation.users'), 'href' => null, 'pending' => true, 'capability' => 'users.view'],
                    ],
                ],
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

it('shares Persian shell presentation data for a Persian request', function (): void {
    $this->app->setLocale('fa');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'fa')
            ->where('direction', 'rtl')
            ->where('navigationLabel', __('app.navigation.label'))
            ->where('navigation', [
                [
                    'key' => 'overview',
                    'label' => __('app.navigation.overview'),
                    'items' => [
                        ['key' => 'dashboard', 'label' => __('navigation.dashboard'), 'href' => route('dashboard')],
                    ],
                ],
                [
                    'key' => 'workspace',
                    'label' => __('app.navigation.workspace'),
                    'items' => [
                        ['key' => 'clients', 'label' => __('navigation.clients'), 'href' => null, 'pending' => true, 'capability' => 'clients.view'],
                        ['key' => 'projects', 'label' => __('navigation.projects'), 'href' => null, 'pending' => true, 'capability' => 'projects.view'],
                        ['key' => 'tasks', 'label' => __('navigation.tasks'), 'href' => null, 'pending' => true, 'capability' => 'tasks.view'],
                    ],
                ],
                [
                    'key' => 'administration',
                    'label' => __('app.navigation.administration'),
                    'items' => [
                        ['key' => 'users', 'label' => __('navigation.users'), 'href' => null, 'pending' => true, 'capability' => 'users.view'],
                    ],
                ],
            ]));
});

it('shares localized user menu interaction labels', function (): void {
    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('translations.app.userMenu.open', __('app.user_menu.open'))
            ->where('translations.app.userMenu.close', __('app.user_menu.close'))
            ->where('translations.app.userMenu.logout', __('app.user_menu.logout'))
            ->where('translations.app.userMenu.switchLocale', __('app.user_menu.switch_locale')));
});
