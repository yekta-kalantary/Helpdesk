<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Identity\Presentation\Livewire\Auth\ResetPassword;

it('allows five password reset submissions before throttling', function (): void {
    $user = User::factory()->create(['email' => 'reset@example.test']);
    foreach (range(1, 5) as $attempt) {
        Livewire::test(ResetPassword::class, ['token' => 'invalid-token'])
            ->set('email', $user->email)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword')
            ->assertHasErrors(['email' => [__('passwords.token')]]);
    }
});

it('rejects a password reset submission after the limit is exceeded', function (): void {
    $user = User::factory()->create(['email' => 'reset@example.test']);
    foreach (range(1, 5) as $attempt) {
        Livewire::test(ResetPassword::class, ['token' => 'invalid-token'])
            ->set('email', $user->email)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword');
    }

    Livewire::test(ResetPassword::class, ['token' => 'invalid-token'])
        ->set('email', $user->email)
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertHasErrors(['email' => [__('identity::messages.too_many_password_reset_attempts')]]);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

it('resets the password and clears the submission limiter after a successful reset', function (): void {
    $user = User::factory()->create(['email' => 'reset@example.test']);
    $token = Password::broker()->createToken($user);
    $key = 'password-reset:'.sha1($user->email.'|'.request()->ip());

    RateLimiter::hit($key, 60);

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue()
        ->and(RateLimiter::tooManyAttempts($key, 5))->toBeFalse();
});
