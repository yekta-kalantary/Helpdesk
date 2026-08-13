<?php

use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
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

it('preserves an existing inactive member when editing unrelated project fields', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Original project name');

    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $member->update(['is_active' => false]);

    $this->actingAs($admin);

    Livewire::test(Form::class, ['project' => $project->id])
        ->assertSee($member->full_name)
        ->set('name', 'Updated project name')
        ->call('save')
        ->assertHasNoErrors();

    expect($project->refresh()->name)->toBe('Updated project name')
        ->and($project->activeMembers()->whereKey($member->id)->exists())->toBeTrue();
});

it('allows an existing inactive member to be removed while editing a project', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Project with inactive member');

    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $member->update(['is_active' => false]);

    $this->actingAs($admin);

    Livewire::test(Form::class, ['project' => $project->id])
        ->set('member_ids', [])
        ->call('save')
        ->assertHasNoErrors();

    expect($project->activeMembers()->whereKey($member->id)->exists())->toBeFalse();
});

it('does not allow a new inactive member to be added through the project form', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $member = User::factory()->customer($client)->inactive()->create();
    $project = mvpProject($client, 'Project without inactive member');

    $this->actingAs($admin);

    Livewire::test(Form::class, ['project' => $project->id])
        ->set('member_ids', [$member->id])
        ->call('save')
        ->assertHasErrors('member_ids');

    expect($project->activeMembers()->whereKey($member->id)->exists())->toBeFalse();
});
