<?php

use Modules\Identity\Infrastructure\Models\User;

it('requires authentication to view the profile page', function (): void {
    $this->get(route('profile.edit'))
        ->assertRedirect(route('login'));
});

it('rejects inactive authenticated users from the profile page', function (): void {
    $user = User::factory()->admin()->inactive()->create();

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertRedirect(route('login'));
});

it('returns the authenticated profile read contract', function (): void {
    $user = User::factory()->admin()->create([
        'name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
        'mobile' => '+15551234567',
    ]);

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('profile.user', [
                'id' => $user->id,
                'name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@example.test',
                'mobile' => '+15551234567',
            ]));
});
