<?php

use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Identity\Presentation\Livewire\Users\Form as UserForm;

it('shows normal users to admin', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $normal = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($normal->email)
        ->assertDontSee($admin->email);
});

it('blocks normal users from user management', function (): void {
    $normal = User::factory()->create();

    $this->actingAs($normal)
        ->get(route('users.index'))
        ->assertForbidden();
});

it('creates the user from general information only', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $this->actingAs($admin);

    Livewire::test(UserForm::class)
        ->set('name', 'علی')
        ->set('last_name', 'احمدی')
        ->call('saveGeneral')
        ->assertHasNoErrors()
        ->assertSet('activeTab', 'contact');

    $user = User::query()
        ->where('name', 'علی')
        ->where('last_name', 'احمدی')
        ->firstOrFail();

    expect($user->email)->toBeNull()
        ->and($user->mobile)->toBeNull()
        ->and($user->getRawOriginal('password'))->toBeNull()
        ->and($user->is_active)->toBeFalse();
});

it('saves contact information without changing general information', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $user = User::factory()->create([
        'name' => 'علی',
        'last_name' => 'احمدی',
        'email' => null,
        'mobile' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(UserForm::class, ['user' => $user->id])
        ->set('email', 'ali@example.test')
        ->set('mobile', '09120000000')
        ->call('saveContact')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('علی')
        ->and($user->last_name)->toBe('احمدی')
        ->and($user->email)->toBe('ali@example.test')
        ->and($user->mobile)->toBe('09120000000');
});

it('saves account settings separately', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $user = User::factory()->create([
        'email' => 'user@example.test',
        'password' => null,
        'is_active' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(UserForm::class, ['user' => $user->id])
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->set('is_active', true)
        ->call('saveAccount')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->is_active)->toBeTrue()
        ->and(Hash::check('new-password', $user->password))->toBeTrue();
});

it('does not activate an incomplete user account', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $user = User::query()->create([
        'name' => 'علی',
        'last_name' => 'احمدی',
        'email' => null,
        'password' => null,
        'is_active' => false,
        'is_admin' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(UserForm::class, ['user' => $user->id])
        ->set('is_active', true)
        ->call('saveAccount')
        ->assertHasErrors(['is_active']);

    expect($user->fresh()->is_active)->toBeFalse();
});
