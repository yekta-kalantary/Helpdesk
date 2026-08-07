<?php

use App\Enums\PersonType;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Modules\Customers\Infrastructure\Models\Customer;
use Modules\Identity\Domain\Access\PermissionCatalog;
use Spatie\Permission\Models\Role;

it('keeps identity data in people instead of users', function (): void {
    expect(Schema::hasTable('people'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'person_id'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'name'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'last_name'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'email'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'mobile'))->toBeFalse();

    expect(fn () => Person::create([
        'type' => PersonType::Employee,
        'first_name' => 'Incomplete',
        'email' => 'incomplete@example.test',
    ]))->toThrow(ValidationException::class);
});

it('does not allow a person type to change after creation', function (): void {
    $person = Person::factory()->customer()->create();

    expect(fn () => $person->update(['type' => PersonType::Employee]))
        ->toThrow(DomainException::class, 'person_type_immutable');
});

it('creates staff as an employee person with a required user account', function (): void {
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

    expect(Person::query()->where('email', 'yekta.staff@example.test')->exists())->toBeFalse();

    $component
        ->set('last_name', 'Kalantary')
        ->set('mobile', '09121234567')
        ->call('save')
        ->assertRedirectToRoute('users.index');

    $person = Person::query()->where('email', 'yekta.staff@example.test')->firstOrFail();
    $user = User::query()->where('person_id', $person->id)->firstOrFail();

    expect($person->type)->toBe(PersonType::Employee)
        ->and($person->first_name)->toBe('Yekta')
        ->and($person->last_name)->toBe('Kalantary')
        ->and($person->mobile)->toBe('09121234567')
        ->and($user->full_name)->toBe('Yekta Kalantary')
        ->and($user->hasRole('developer'))->toBeTrue();
});

it('keeps employee person and user when access is disabled', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    $role = Role::findOrCreate('developer', 'web');
    $person = Person::factory()->create([
        'type' => PersonType::Employee,
        'email' => 'disabled.employee@example.test',
    ]);
    $user = User::factory()->for($person)->create(['is_active' => true]);
    $user->assignRole($role);

    Livewire::actingAs($admin)
        ->test('identity::users.form', ['user' => $user->id])
        ->set('is_active', false)
        ->call('save')
        ->assertRedirectToRoute('users.index');

    expect(Person::query()->whereKey($person->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue()
        ->and(User::query()->findOrFail($user->id)->is_active)->toBeFalse();
});

it('creates customers without a user account unless portal access is enabled', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test('customers::form')
        ->set('name', 'NoPortal')
        ->set('last_name', 'Customer')
        ->set('email', 'noportal.customer@example.test')
        ->set('mobile', '09121110000')
        ->call('save')
        ->assertRedirectToRoute('customers.index');

    $person = Person::query()->where('email', 'noportal.customer@example.test')->firstOrFail();

    expect($person->type)->toBe(PersonType::Customer)
        ->and(User::query()->where('person_id', $person->id)->exists())->toBeFalse();
});

it('updates portal password controls immediately when portal access is toggled', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    $component = Livewire::actingAs($admin)
        ->test('customers::form')
        ->assertSeeHtml('wire:model.live="portal_enabled"')
        ->assertSeeHtml('aria-disabled="true"');

    $component
        ->set('portal_enabled', true)
        ->assertSet('portal_enabled', true)
        ->assertDontSeeHtml('aria-disabled="true"');
});

it('uses the same customer person when portal access is enabled', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test('customers::form')
        ->set('name', 'Portal')
        ->set('last_name', 'Customer')
        ->set('email', 'portal.customer@example.test')
        ->set('mobile', '09121112233')
        ->set('portal_enabled', true)
        ->set('portal_password', 'password123')
        ->set('portal_password_confirmation', 'password123')
        ->call('save')
        ->assertRedirectToRoute('customers.index');

    $person = Person::query()->where('email', 'portal.customer@example.test')->firstOrFail();
    $user = User::query()->where('person_id', $person->id)->firstOrFail();

    expect($person->type)->toBe(PersonType::Customer)
        ->and($user->person_id)->toBe($person->id)
        ->and($user->hasRole('customer'))->toBeTrue();
});

it('keeps customer, person and portal user when portal access is disabled', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test('customers::form')
        ->set('name', 'Persistent')
        ->set('last_name', 'Customer')
        ->set('email', 'persistent.customer@example.test')
        ->set('mobile', '09123334444')
        ->set('portal_enabled', true)
        ->set('portal_password', 'password123')
        ->set('portal_password_confirmation', 'password123')
        ->call('save')
        ->assertRedirectToRoute('customers.index');

    $person = Person::query()->where('email', 'persistent.customer@example.test')->firstOrFail();
    $customer = Customer::query()->where('person_id', $person->id)->firstOrFail();
    $user = User::query()->where('person_id', $person->id)->firstOrFail();

    Livewire::actingAs($admin)
        ->test('customers::form', ['customer' => $customer->id])
        ->set('portal_enabled', false)
        ->call('save')
        ->assertRedirectToRoute('customers.index');

    expect(Person::query()->whereKey($person->id)->exists())->toBeTrue()
        ->and(Customer::query()->whereKey($customer->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue()
        ->and(User::query()->findOrFail($user->id)->is_active)->toBeFalse();
});

it('does not define delete permissions for customers or employees', function (): void {
    expect(PermissionCatalog::all())
        ->not->toContain('customers.delete')
        ->not->toContain('users.delete');
});

it('shows a success flash only once on its destination page', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();
    $message = 'Saved exactly once';

    $response = $this->actingAs($admin)
        ->withSession(['success' => $message])
        ->get(route('customers.index'));

    $response->assertOk();

    expect(substr_count($response->getContent(), $message))->toBe(1);
});

it('authenticates accounts by the email stored on their person', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    Livewire::test('identity::auth.login')
        ->set('email', $admin->email)
        ->set('password', config('helpdesk.admin.password'))
        ->call('login')
        ->assertRedirect(route('dashboard'));
});
