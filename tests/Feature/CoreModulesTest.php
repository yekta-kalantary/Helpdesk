<?php

use Illuminate\Support\Facades\DB;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;

it('exposes the MVP admin modules', function (): void {
    $admin = User::query()->admins()->firstOrFail();

    $this->actingAs($admin)->get(route('clients.index'))->assertOk();
    $this->get(route('users.index'))->assertOk();
    $this->get(route('projects.index'))->assertOk();
    $this->get(route('tasks.index'))->assertOk();
    $this->get(route('notifications.index'))->assertOk();
});

it('keeps authenticated identity separate from the client account', function (): void {
    $client = Client::factory()->create(['name' => 'Acme']);
    $user = User::factory()->customer($client)->create([
        'name' => 'Yekta',
        'last_name' => 'Kalantary',
        'email' => 'yekta@example.test',
        'mobile' => '09120000000',
    ]);

    expect($user->full_name)->toBe('Yekta Kalantary')
        ->and($user->client->is($client))->toBeTrue()
        ->and($user->client->name)->toBe('Acme');
});

it('links customer users to projects through auditable active membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Example project');

    app(ProjectMembershipManager::class)->add($project, $customer, $admin);

    $row = DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->first();

    expect($project->hasActiveMember($customer))->toBeTrue()
        ->and($row)->not->toBeNull()
        ->and($row->joined_at)->not->toBeNull()
        ->and($row->removed_at)->toBeNull();
});
