<?php

use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Presentation\Livewire\Form;

it('renders project form sections in order with preserved bindings and mobile actions', function (): void {
    $admin = User::query()->admins()->firstOrFail();

    $html = $this->actingAs($admin)
        ->get(route('projects.create'))
        ->assertSuccessful()
        ->getContent();

    $identity = strpos($html, '۱. هویت پروژه');
    $context = strpos($html, '۲. زمینه پروژه');
    $membership = strpos($html, '۳. عضویت');
    $schedule = strpos($html, '۴. زمان‌بندی');

    expect($identity)->toBeInt()
        ->and($context)->toBeInt()
        ->and($membership)->toBeInt()
        ->and($schedule)->toBeInt()
        ->and($identity)->toBeLessThan($context)
        ->and($context)->toBeLessThan($membership)
        ->and($membership)->toBeLessThan($schedule)
        ->and($html)
        ->toContain('wire:submit="save"')
        ->toContain('wire:model="name"')
        ->toContain('wire:model.live.number="client_id"')
        ->toContain('wire:model="description"')
        ->toContain('wire:model="start_date"')
        ->toContain('wire:model="due_date"')
        ->toMatch('/<div[^>]*sticky bottom-0 z-20[^>]*pb-\[calc\(0\.5rem\+env\(safe-area-inset-bottom\)\)\][^>]*>.*?wire:target="save"/s');
});

it('keeps sticky form actions enabled for existing client forms', function (): void {
    $admin = User::query()->admins()->firstOrFail();

    $html = $this->actingAs($admin)
        ->get(route('clients.create'))
        ->assertSuccessful()
        ->getContent();

    expect($html)->toContain('sticky bottom-0 z-20');
});

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
