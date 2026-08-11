<?php

use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Presentation\Livewire\Form;

it('does not create a project when requested members are not eligible for its client', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $outsider = User::factory()->customer($clientB)->create();

    $this->actingAs($admin);

    Livewire::test(Form::class)
        ->set('client_id', $clientA->id)
        ->set('name', 'Must not be created')
        ->set('member_ids', [$outsider->id])
        ->call('save')
        ->assertHasErrors('member_ids');

    expect(Project::query()->where('name', 'Must not be created')->exists())->toBeFalse();
});

it('does not update a project when requested members are not eligible for its client', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $outsider = User::factory()->customer($clientB)->create();
    $project = mvpProject($clientA, 'Original project name');

    $this->actingAs($admin);

    Livewire::test(Form::class, ['project' => $project->id])
        ->set('name', 'Must not be persisted')
        ->set('member_ids', [$outsider->id])
        ->call('save')
        ->assertHasErrors('member_ids');

    expect($project->refresh()->name)->toBe('Original project name');
});
