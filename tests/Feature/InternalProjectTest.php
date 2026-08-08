<?php

use App\Enums\PersonType;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\Projects\Application\Queries\ProjectFormOptions;

it('creates an internal project without a customer', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test('projects::form')
        ->set('title', 'Internal Operations')
        ->set('category', 'internal')
        ->set('type', 'support')
        ->set('status', 'active')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('projects.index');

    $project = DB::table('projects')->where('title', 'Internal Operations')->first();

    expect($project)->not->toBeNull()
        ->and($project->category)->toBe('internal')
        ->and($project->customer_id)->toBeNull();
});

it('requires a customer for customer projects', function (): void {
    $admin = User::query()->role('admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test('projects::form')
        ->set('title', 'Customer Project Without Customer')
        ->set('category', 'customer')
        ->set('customer_id', null)
        ->set('type', 'support')
        ->set('status', 'active')
        ->call('save')
        ->assertHasErrors(['customer_id' => 'required']);

    expect(DB::table('projects')->where('title', 'Customer Project Without Customer')->exists())->toBeFalse();
});

it('does not expose internal projects to customer portal users', function (): void {
    $person = Person::create([
        'type' => PersonType::Customer,
        'first_name' => 'Portal',
        'last_name' => 'Customer',
        'email' => 'internal-project-visibility@example.test',
        'mobile' => '09120000001',
    ]);
    $customerUser = User::factory()->for($person)->create(['is_active' => true]);
    $customerUser->assignRole('customer');

    $customerId = DB::table('customers')->insertGetId([
        'person_id' => $person->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('projects')->insert([
        [
            'customer_id' => $customerId,
            'category' => 'customer',
            'title' => 'Visible Customer Project',
            'type' => 'support',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'customer_id' => null,
            'category' => 'internal',
            'title' => 'Hidden Internal Project',
            'type' => 'support',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($customerUser)
        ->get('/projects')
        ->assertOk()
        ->assertSee('Visible Customer Project')
        ->assertDontSee('Hidden Internal Project');
});

it('searches customers without loading the full customer list', function (): void {
    foreach (range(1, 30) as $index) {
        $personId = DB::table('people')->insertGetId([
            'type' => PersonType::Customer->value,
            'first_name' => 'Searchable',
            'last_name' => $index === 30 ? 'Needle Customer' : "Customer {$index}",
            'email' => "search-customer-{$index}@example.test",
            'mobile' => '09'.str_pad((string) $index, 9, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')->insert([
            'person_id' => $personId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $options = app(ProjectFormOptions::class)->get('Needle');

    expect($options['customers'])->toHaveCount(1)
        ->and($options['customers'][0]['name'])->toBe('Searchable Needle Customer');

    expect(app(ProjectFormOptions::class)->get()['customers'])->toHaveCount(25);
});
