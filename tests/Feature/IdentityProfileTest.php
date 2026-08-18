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

it('updates personal information without changing contact information', function (): void {
    $user = User::factory()->admin()->create([
        'name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
        'mobile' => '+15551234567',
    ]);

    $this->actingAs($user)
        ->post(route('profile.personal.update'), [
            'name' => 'Grace',
            'last_name' => 'Hopper',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', __('identity::messages.general_saved'));

    expect($user->refresh()->only(['name', 'last_name', 'email', 'mobile']))
        ->toBe([
            'name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'ada@example.test',
            'mobile' => '+15551234567',
        ]);
});

it('updates contact information without changing personal information', function (): void {
    $user = User::factory()->admin()->create([
        'name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
        'mobile' => '+15551234567',
    ]);

    $this->actingAs($user)
        ->post(route('profile.contact.update'), [
            'email' => 'grace@example.test',
            'mobile' => '+15557654321',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', __('identity::messages.contact_saved'));

    expect($user->refresh()->only(['name', 'last_name', 'email', 'mobile']))
        ->toBe([
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'grace@example.test',
            'mobile' => '+15557654321',
        ]);
});

it('allows a user to keep their current email address', function (): void {
    $user = User::factory()->admin()->create(['email' => 'ada@example.test']);

    $this->actingAs($user)
        ->post(route('profile.contact.update'), [
            'email' => 'ada@example.test',
        ])
        ->assertRedirect(route('profile.edit'));
});

it('requires a personal name', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('profile.personal.update'), ['last_name' => 'Lovelace'])
        ->assertSessionHasErrors('name');
});

it('requires a personal last name', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('profile.personal.update'), ['name' => 'Ada'])
        ->assertSessionHasErrors('last_name');
});

it('rejects an invalid contact email', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('profile.contact.update'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});

it('rejects a duplicate contact email', function (): void {
    $existingUser = User::factory()->admin()->create();
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('profile.contact.update'), ['email' => $existingUser->email])
        ->assertSessionHasErrors('email');
});

it('rejects an overlong mobile number', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('profile.contact.update'), [
            'email' => 'ada@example.test',
            'mobile' => str_repeat('1', 33),
        ])
        ->assertSessionHasErrors('mobile');
});
