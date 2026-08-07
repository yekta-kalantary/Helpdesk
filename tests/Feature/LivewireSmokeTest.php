<?php

use App\Models\User;

it('renders the guest Livewire login page', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSeeLivewire('identity::auth.login');
});

it('renders every primary admin page as Livewire', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    $pages = [
        [route('dashboard'), 'identity::dashboard'],
        [route('customers.index'), 'customers::index'],
        [route('customers.create'), 'customers::form'],
        [route('projects.index'), 'projects::index'],
        [route('projects.create'), 'projects::form'],
        [route('tasks.index'), 'tasks::index'],
        [route('tasks.create'), 'tasks::form'],
        [route('tickets.index'), 'tickets::index'],
        [route('tickets.create'), 'tickets::create'],
        [route('users.index'), 'identity::users.index'],
        [route('users.create'), 'identity::users.form'],
        [route('roles.index'), 'identity::roles.index'],
        [route('roles.create'), 'identity::roles.form'],
        [route('notifications.index'), 'identity::notifications.index'],
        [route('settings.smtp.edit'), 'settings::smtp'],
        [route('reports.index'), 'reports::index'],
    ];

    foreach ($pages as [$url, $component]) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertSeeLivewire($component);
    }
});
