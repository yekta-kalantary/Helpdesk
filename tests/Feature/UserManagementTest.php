<?php

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Identity\Presentation\Livewire\Profile;
use Modules\Identity\Presentation\Livewire\Users\Form as UserForm;
use Modules\Identity\Presentation\Livewire\Users\Index as UsersIndex;
use Modules\Identity\Presentation\Livewire\Users\Show as UserShow;
use Modules\Projects\Application\ProjectMembershipManager;

it('shows customer users to admin and hides system admins from customer management', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($customer->email)
        ->assertSee(route('users.show', $customer->id))
        ->assertDontSee($admin->email);
});

it('keeps user management controls and identity form labels admin-only', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer(Client::factory()->create())->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertSee(route('users.create'))
        ->assertSee('همه وضعیت‌ها')
        ->assertSee('آخرین ورود')
        ->assertSee('wire:navigate', false)
        ->assertSee('min-h-11', false)
        ->assertSee('text-text', false);

    $this->actingAs($customer)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('name="name"', false)
        ->assertSee('name="last_name"', false)
        ->assertSee(__('app.save'))
        ->assertSee('aria-labelledby="profile-identity-heading"', false)
        ->assertSee('autocomplete="new-password"', false)
        ->assertSee('min-h-11', false);

    $this->actingAs($customer)->get(route('users.index'))->assertForbidden();
});

it('filters customer users by client and status while retaining search', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create(['name' => 'Acme Client']);
    $otherClient = Client::factory()->create(['name' => 'Other Client']);
    $match = User::factory()->customer($client)->create([
        'name' => 'Ali',
        'last_name' => 'Match',
        'email' => 'ali-match@example.test',
    ]);
    $wrongStatus = User::factory()->customer($client)->inactive()->create([
        'name' => 'Ali',
        'email' => 'ali-inactive@example.test',
    ]);
    $wrongClient = User::factory()->customer($otherClient)->create([
        'name' => 'Ali',
        'email' => 'ali-other@example.test',
    ]);

    Livewire::actingAs($admin)
        ->test(UsersIndex::class)
        ->set('q', 'Ali')
        ->set('client', (string) $client->id)
        ->set('status', 'active')
        ->assertSee($match->email)
        ->assertDontSee($wrongStatus->email)
        ->assertDontSee($wrongClient->email);
});

it('ignores invalid user list filter values and resets pagination when filters change', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();

    Livewire::actingAs($admin)
        ->withQueryParams(['client' => '999999', 'status' => 'unknown'])
        ->test(UsersIndex::class)
        ->assertSet('client', '')
        ->assertSet('status', '')
        ->call('gotoPage', 3)
        ->set('client', (string) $client->id)
        ->assertSet('paginators.page', 1)
        ->call('gotoPage', 3)
        ->set('status', 'inactive')
        ->assertSet('paginators.page', 1);
});

it('blocks customers from user management', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $other = User::factory()->customer($client)->create();

    $this->actingAs($customer)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->get(route('users.show', $other->id))->assertForbidden();
});

it('creates a customer under an active client and sends account setup mail', function (): void {
    Notification::fake();
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $this->actingAs($admin);

    Livewire::test(UserForm::class)
        ->set('client_id', $client->id)
        ->set('name', 'علی')
        ->set('last_name', 'احمدی')
        ->set('email', ' Ali@Example.Test ')
        ->set('mobile', '09120000000')
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $user = User::query()->where('email', 'ali@example.test')->firstOrFail();

    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->client_id)->toBe($client->id)
        ->and($user->getRawOriginal('password'))->toBeNull()
        ->and($user->is_active)->toBeTrue();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('keeps client and role immutable in the admin user profile flow', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create([
        'name' => 'علی',
        'last_name' => 'احمدی',
        'email' => 'ali@example.test',
    ]);
    $this->actingAs($admin);

    Livewire::test(UserShow::class, ['user' => $customer->id])
        ->set('name', 'رضا')
        ->set('last_name', 'رضایی')
        ->set('email', ' Reza@Example.Test ')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('saveProfile')
        ->assertHasNoErrors();

    $customer->refresh();

    expect($customer->name)->toBe('رضا')
        ->and($customer->email)->toBe('reza@example.test')
        ->and($customer->client_id)->toBe($client->id)
        ->and($customer->role)->toBe(UserRole::Customer)
        ->and(Hash::check('new-password', $customer->password))->toBeTrue();
});

it('shows only active project memberships on the customer identity page', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $activeProject = mvpProject($client, 'Active membership');
    $removedProject = mvpProject($client, 'Removed membership');
    $manager = app(ProjectMembershipManager::class);
    $manager->add($activeProject, $customer, $admin);
    $manager->add($removedProject, $customer, $admin);
    $manager->remove($removedProject, $customer, $admin);

    $this->actingAs($admin)
        ->get(route('users.show', $customer))
        ->assertOk()
        ->assertSee('Active membership')
        ->assertDontSee('Removed membership');
});

it('lets a customer update own name and password without exposing account boundaries', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create([
        'name' => 'Old',
        'last_name' => 'Name',
    ]);
    $this->actingAs($customer);

    Livewire::test(Profile::class)
        ->set('name', 'New')
        ->set('last_name', 'Name')
        ->set('password', 'customer-password')
        ->set('password_confirmation', 'customer-password')
        ->call('save')
        ->assertHasNoErrors();

    $customer->refresh();

    expect($customer->name)->toBe('New')
        ->and($customer->client_id)->toBe($client->id)
        ->and($customer->role)->toBe(UserRole::Customer)
        ->and(Hash::check('customer-password', $customer->password))->toBeTrue();
});
