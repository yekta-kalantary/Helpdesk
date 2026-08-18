<?php

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Modules\Identity\Infrastructure\Models\User;

it('renders the password recovery page for guests', function (): void {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
            ->where('auth.canLogin', true)
            ->where('auth.direction', 'rtl')
        );
});

it('sends a password reset notification for an existing account', function (): void {
    Notification::fake();
    $user = User::factory()->admin()->create(['email' => 'admin@example.test']);

    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => 'admin@example.test',
    ]);

    $response->assertRedirect(route('password.request'))
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns the same confirmation for an unknown account', function (): void {
    Notification::fake();

    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => 'unknown@example.test',
    ]);

    $response->assertRedirect(route('password.request'))
        ->assertSessionHas('status');

    Notification::assertNothingSent();
});

it('rejects an invalid email address', function (): void {
    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'invalid-email'])
        ->assertRedirect(route('password.request'))
        ->assertSessionHasErrors('email');
});
