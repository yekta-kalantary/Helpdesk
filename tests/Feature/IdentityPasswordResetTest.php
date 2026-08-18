<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Modules\Identity\Infrastructure\Models\User;

it('renders the password reset page with the route token and email', function (): void {
    $this->get(route('password.reset', [
        'token' => 'reset-token',
        'email' => 'admin@example.test',
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
            ->where('reset.email', 'admin@example.test')
            ->where('reset.token', 'reset-token')
            ->where('auth.direction', 'rtl')
        );
});

it('resets the password with a valid token', function (): void {
    $user = User::factory()->admin()->create([
        'email' => 'admin@example.test',
        'password' => 'old-password',
    ]);
    $token = Password::broker()->createToken($user);

    $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->post(route('password.update', ['token' => $token]), [
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('status');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('rejects an invalid reset token', function (): void {
    $this->from(route('password.reset', ['token' => 'invalid-token', 'email' => 'admin@example.test']))
        ->post(route('password.update', ['token' => 'invalid-token']), [
            'email' => 'admin@example.test',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('token');
});

it('validates password confirmation', function (): void {
    $user = User::factory()->admin()->create(['email' => 'admin@example.test']);
    $token = Password::broker()->createToken($user);

    $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->post(route('password.update', ['token' => $token]), [
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('password');
});
