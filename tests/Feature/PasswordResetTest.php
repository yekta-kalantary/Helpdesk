<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Identity\Presentation\Livewire\Auth\ResetPassword;

it('renders accessible guest auth forms with labels, actions, and loading states', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('for="email"', false)
        ->assertSee('name="email"', false)
        ->assertSee(__('app.login'))
        ->assertSee('wire:submit="login"', false)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="login"', false);

    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('for="email"', false)
        ->assertSee('name="email"', false)
        ->assertSee('ارسال لینک بازیابی')
        ->assertSee('wire:submit="send"', false)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="send"', false);

    $this->get(route('password.reset', ['token' => 'test-token']).'?email=reset@example.test')
        ->assertOk()
        ->assertSee('for="email"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('ذخیره رمز عبور')
        ->assertSee('wire:submit="resetPassword"', false)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="resetPassword"', false);
});

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
