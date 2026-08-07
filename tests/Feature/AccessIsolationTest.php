<?php

use App\Enums\PersonType;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function createPortalCustomer(string $email, string $name): array
{
    [$firstName, $lastName] = array_pad(explode(' ', $name, 2), 2, 'Customer');

    $person = Person::create([
        'type' => PersonType::Customer,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'mobile' => fake()->unique()->numerify('09#########'),
    ]);

    $user = User::factory()->for($person)->create(['is_active' => true]);
    $user->assignRole('customer');

    $customerId = DB::table('customers')->insertGetId([
        'person_id' => $person->id,
        'status' => 'active',
        'notes' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$user, $customerId];
}

function createProjectForCustomer(int $customerId, string $title): int
{
    return DB::table('projects')->insertGetId([
        'customer_id' => $customerId,
        'title' => $title,
        'type' => 'support',
        'description' => null,
        'status' => 'active',
        'starts_at' => null,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('shows a customer only their own projects', function (): void {
    [$customerUser, $customerId] = createPortalCustomer('customer-one@example.com', 'Customer One');
    [, $otherCustomerId] = createPortalCustomer('customer-two@example.com', 'Customer Two');

    createProjectForCustomer($customerId, 'Visible Customer Project');
    createProjectForCustomer($otherCustomerId, 'Hidden Other Project');

    $this->actingAs($customerUser)
        ->get('/projects')
        ->assertOk()
        ->assertSeeLivewire('projects::index')
        ->assertSee('Visible Customer Project')
        ->assertDontSee('Hidden Other Project');
});

it('shows a customer only explicitly visible tasks in their own projects', function (): void {
    [$customerUser, $customerId] = createPortalCustomer('task-customer@example.com', 'Task Customer');
    [, $otherCustomerId] = createPortalCustomer('other-task-customer@example.com', 'Other Task Customer');

    $projectId = createProjectForCustomer($customerId, 'Customer Task Project');
    $otherProjectId = createProjectForCustomer($otherCustomerId, 'Other Task Project');
    $admin = User::query()->role('admin')->firstOrFail();

    $visibleTaskId = DB::table('tasks')->insertGetId([
        'project_id' => $projectId,
        'title' => 'Visible Portal Task',
        'description' => null,
        'assigned_to' => null,
        'created_by' => $admin->id,
        'priority' => 'medium',
        'status' => 'todo',
        'is_customer_visible' => true,
        'due_at' => null,
        'estimated_minutes' => null,
        'spent_minutes' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $internalTaskId = DB::table('tasks')->insertGetId([
        'project_id' => $projectId,
        'title' => 'Internal Team Task',
        'description' => null,
        'assigned_to' => $admin->id,
        'created_by' => $admin->id,
        'priority' => 'high',
        'status' => 'in_progress',
        'is_customer_visible' => false,
        'due_at' => null,
        'estimated_minutes' => 120,
        'spent_minutes' => 30,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tasks')->insert([
        'project_id' => $otherProjectId,
        'title' => 'Other Customer Visible Task',
        'description' => null,
        'assigned_to' => null,
        'created_by' => $admin->id,
        'priority' => 'low',
        'status' => 'todo',
        'is_customer_visible' => true,
        'due_at' => null,
        'estimated_minutes' => null,
        'spent_minutes' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($customerUser)
        ->get('/tasks')
        ->assertOk()
        ->assertSeeLivewire('tasks::index')
        ->assertSee('Visible Portal Task')
        ->assertDontSee('Internal Team Task')
        ->assertDontSee('Other Customer Visible Task');

    $this->actingAs($customerUser)->get("/tasks/{$visibleTaskId}")->assertOk()->assertSeeLivewire('tasks::show');
    $this->actingAs($customerUser)->get("/tasks/{$internalTaskId}")->assertNotFound();
});

it('shows a customer only their own tickets', function (): void {
    [$customerUser, $customerId] = createPortalCustomer('ticket-customer@example.com', 'Ticket Customer');
    [, $otherCustomerId] = createPortalCustomer('other-ticket-customer@example.com', 'Other Ticket Customer');

    $projectId = createProjectForCustomer($customerId, 'Ticket Project');
    $otherProjectId = createProjectForCustomer($otherCustomerId, 'Other Ticket Project');

    DB::table('tickets')->insert([
        [
            'customer_id' => $customerId,
            'project_id' => $projectId,
            'created_by' => $customerUser->id,
            'assigned_to' => null,
            'subject' => 'My Visible Ticket',
            'category' => 'technical',
            'priority' => 'medium',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'customer_id' => $otherCustomerId,
            'project_id' => $otherProjectId,
            'created_by' => User::query()->role('admin')->firstOrFail()->id,
            'assigned_to' => null,
            'subject' => 'Other Customer Ticket',
            'category' => 'general',
            'priority' => 'low',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($customerUser)
        ->get('/tickets')
        ->assertOk()
        ->assertSeeLivewire('tickets::index')
        ->assertSee('My Visible Ticket')
        ->assertDontSee('Other Customer Ticket');
});

it('prevents scoped staff from creating tickets for unrelated customers', function (): void {
    [, $allowedCustomerId] = createPortalCustomer('allowed-ticket-customer@example.com', 'Allowed Ticket Customer');
    [, $blockedCustomerId] = createPortalCustomer('blocked-ticket-customer@example.com', 'Blocked Ticket Customer');

    $allowedProjectId = createProjectForCustomer($allowedCustomerId, 'Allowed Staff Project');
    $blockedProjectId = createProjectForCustomer($blockedCustomerId, 'Blocked Staff Project');

    $role = Role::findOrCreate('support-agent', 'web');
    $role->syncPermissions(['tickets.view', 'tickets.create', 'tickets.reply']);

    $staff = User::factory()
        ->for(Person::factory()->state(['email' => 'support-agent@example.com']))
        ->create(['is_active' => true]);
    $staff->assignRole($role);

    DB::table('project_user')->insert([
        'project_id' => $allowedProjectId,
        'user_id' => $staff->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($staff)
        ->test('tickets::create')
        ->set('customer_id', $blockedCustomerId)
        ->set('project_id', $blockedProjectId)
        ->set('subject', 'Forged Ticket')
        ->set('category', 'technical')
        ->set('priority', 'medium')
        ->set('body', 'This request must be rejected by server-side scope enforcement.')
        ->call('save')
        ->assertForbidden();

    expect(DB::table('tickets')->where('subject', 'Forged Ticket')->exists())->toBeFalse();
});
