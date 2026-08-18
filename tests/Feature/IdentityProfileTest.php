<?php

use Illuminate\Support\Facades\Hash;
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
            ])
            ->where('translations.identity.profile.personal.title', __('identity::messages.profile.personal.title'))
            ->where('translations.identity.profile.contact.title', __('identity::messages.profile.contact.title'))
            ->where('translations.identity.profile.password.title', __('identity::messages.profile.password.title'))
            ->where('translations.identity.profile.personal.saved', __('identity::messages.profile.personal.saved'))
            ->where('translations.identity.profile.contact.saved', __('identity::messages.profile.contact.saved'))
            ->where('translations.identity.profile.password.saved', __('identity::messages.profile.password.saved'))
            ->missing('profile.user.email_verified_at')
            ->missing('translations.identity.profile.verification'));
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

it('updates the authenticated user password and keeps the session authenticated', function (): void {
    $user = User::factory()->admin()->create([
        'password' => 'current-password',
        'remember_token' => 'old-token',
        'name' => 'Ada',
    ]);

    $this->actingAs($user)
        ->post(route('profile.password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', __('identity::messages.password_saved'));

    $updatedUser = $user->refresh();

    expect(Hash::check('new-password', $updatedUser->password))->toBeTrue()
        ->and($updatedUser->remember_token)->not->toBe('old-token')
        ->and($updatedUser->name)->toBe('Ada');

    $this->assertAuthenticatedAs($user);
});

it('rejects an incorrect current password', function (): void {
    $user = User::factory()->admin()->create(['password' => 'current-password']);

    $this->actingAs($user)
        ->post(route('profile.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors([
            'current_password' => __('identity::messages.validation.current_password'),
        ]);
});

it('rejects a weak password', function (): void {
    $user = User::factory()->admin()->create(['password' => 'current-password']);

    $this->actingAs($user)
        ->post(route('profile.password.update'), [
            'current_password' => 'current-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors('password');
});

it('rejects a password confirmation mismatch', function (): void {
    $user = User::factory()->admin()->create(['password' => 'current-password']);

    $this->actingAs($user)
        ->post(route('profile.password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])
        ->assertSessionHasErrors('password');
});
