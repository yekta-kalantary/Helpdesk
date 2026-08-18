<?php

use Illuminate\Support\Facades\Hash;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

it('renders the identity login page for guests', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
            ->where('auth.canResetPassword', true)
            ->where('auth.canRememberSession', true)
            ->where('auth.direction', 'rtl')
        );
});

it('authenticates an active user and regenerates the session', function (): void {
    $user = User::factory()->admin()->create([
        'email' => 'admin@example.test',
        'password' => Hash::make('secret-password'),
        'last_login_at' => null,
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'admin@example.test',
        'password' => 'secret-password',
        'remember' => true,
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->last_login_at)->not->toBeNull();
});

it('authenticates an active employee with an active client', function (): void {
    $employee = User::factory()->employee(Client::factory()->create())->create([
        'email' => 'employee@example.test',
        'password' => Hash::make('secret-password'),
    ]);

    $this->post(route('login.store'), [
        'email' => 'employee@example.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($employee);
});

it('rejects invalid credentials without authenticating the user', function (): void {
    User::factory()->admin()->create([
        'email' => 'admin@example.test',
        'password' => Hash::make('secret-password'),
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rejects inactive accounts after valid credentials', function (): void {
    User::factory()->admin()->inactive()->create([
        'email' => 'inactive@example.test',
        'password' => Hash::make('secret-password'),
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => 'inactive@example.test',
            'password' => 'secret-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
