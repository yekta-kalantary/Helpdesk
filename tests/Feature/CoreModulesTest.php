<?php

use Illuminate\Support\Facades\DB;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Projects\Application\ProjectMembershipManager;

it('keeps authenticated identity separate from the client account', function (): void {
    $client = Client::factory()->create(['name' => 'Acme']);
    $user = User::factory()->customer($client)->create([
        'name' => 'Yekta',
        'last_name' => 'Kalantary',
        'email' => 'yekta@example.test',
        'mobile' => '09120000000',
    ]);

    $account = app(AccountDirectory::class)->find($user->id);

    expect($user->full_name)->toBe('Yekta Kalantary')
        ->and($account?->clientId)->toBe($client->id);
});

it('links customer users to projects through auditable active membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Example project');

    app(ProjectMembershipManager::class)->add($project, $customer->id, $admin->id);

    $row = DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $customer->id)
        ->first();

    expect(app(ProjectMembershipDirectory::class)->hasActiveMembership($project->id, $customer->id))->toBeTrue()
        ->and($row)->not->toBeNull()
        ->and($row->joined_at)->not->toBeNull()
        ->and($row->removed_at)->toBeNull();
});
