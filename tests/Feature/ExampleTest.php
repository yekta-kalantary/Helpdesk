<?php

use App\Models\User;

it('redirects guests to the web login page', function (): void {
    $this->get('/')
        ->assertRedirect(route('login'));

    $this->get('/login')
        ->assertOk()
        ->assertSee(__('identity::messages.login_title'));
});

it('allows the seeded admin to open the dashboard', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(__('app.dashboard'));
});
