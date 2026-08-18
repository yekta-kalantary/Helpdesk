<?php

use Modules\Identity\Infrastructure\Models\User;

it('logs out an authenticated user and invalidates the session', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);
    $sessionId = session()->getId();

    $this->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
    expect(session()->getId())->not->toBe($sessionId);
    expect(session()->token())->not->toBeNull();
});

it('requires authentication to log out', function (): void {
    $this->post(route('logout'))
        ->assertRedirect(route('login'));
});
