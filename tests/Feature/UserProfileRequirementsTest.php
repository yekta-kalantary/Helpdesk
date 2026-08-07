<?php

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('rejects incomplete user models regardless of creation path', function (): void {
    expect(fn () => User::create([
        'name' => 'Incomplete',
        'email' => 'incomplete@example.test',
        'password' => 'password123',
        'is_active' => true,
    ]))->toThrow(ValidationException::class);

    expect(User::query()->where('email', 'incomplete@example.test')->exists())->toBeFalse();
});

it('requires first name last name email and mobile for staff users', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    Role::findOrCreate('developer', 'web');

    $component = Livewire::actingAs($admin)
        ->test('identity::users.form')
        ->set('name', 'Yekta')
        ->set('email', 'yekta.staff@example.test')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role', 'developer')
        ->call('save')
        ->assertHasErrors(['last_name' => 'required', 'mobile' => 'required']);

    expect(User::query()->where('email', 'yekta.staff@example.test')->exists())->toBeFalse();

    $component
        ->set('last_name', 'Kalantary')
        ->set('mobile', '09121234567')
        ->call('save')
        ->assertRedirectToRoute('users.index');

    $user = User::query()->where('email', 'yekta.staff@example.test')->firstOrFail();

    expect($user->name)->toBe('Yekta')
        ->and($user->last_name)->toBe('Kalantary')
        ->and($user->mobile)->toBe('09121234567')
        ->and($user->full_name)->toBe('Yekta Kalantary');
});

it('requires a complete user profile when customer portal access is enabled', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    $component = Livewire::actingAs($admin)
        ->test('customers::form')
        ->set('name', 'Portal')
        ->set('email', 'portal.customer@example.test')
        ->set('portal_enabled', true)
        ->set('portal_password', 'password123')
        ->set('portal_password_confirmation', 'password123')
        ->call('save')
        ->assertHasErrors(['portal_last_name' => 'required', 'portal_mobile' => 'required']);

    expect(User::query()->where('email', 'portal.customer@example.test')->exists())->toBeFalse();

    $component
        ->set('portal_last_name', 'Customer')
        ->set('portal_mobile', '09121112233')
        ->call('save')
        ->assertRedirectToRoute('customers.index');

    $user = User::query()->where('email', 'portal.customer@example.test')->firstOrFail();

    expect($user->name)->toBe('Portal')
        ->and($user->last_name)->toBe('Customer')
        ->and($user->mobile)->toBe('09121112233')
        ->and($user->hasRole('customer'))->toBeTrue();
});
