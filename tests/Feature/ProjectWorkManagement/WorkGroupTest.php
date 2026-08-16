<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Projects\Infrastructure\Models\WorkGroup;

it('supports work group trees up to five levels and rejects level six', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $manager = app(WorkGroupManager::class);

    $parent = null;
    foreach (range(1, 5) as $level) {
        $parent = $manager->create($admin, $project, [
            'title' => "Level {$level}",
            'parent_id' => $parent?->id,
        ]);
        expect($parent->depth())->toBe($level);
    }

    expect(fn () => $manager->create($admin, $project, [
        'title' => 'Level 6',
        'parent_id' => $parent->id,
    ]))->toThrow(DomainException::class);
});

it('rejects work group cycles cross project moves and customer mutations', function (): void {
    $client = Client::factory()->create();
    $project = mvpProject($client, 'A');
    $otherProject = mvpProject($client, 'B');
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $manager = app(WorkGroupManager::class);

    $root = $manager->create($admin, $project, ['title' => 'Root']);
    $child = $manager->create($admin, $project, ['title' => 'Child', 'parent_id' => $root->id]);
    $foreign = $manager->create($admin, $otherProject, ['title' => 'Foreign']);

    expect(fn () => $manager->move($admin, $root, $child))->toThrow(DomainException::class)
        ->and(fn () => $manager->move($admin, $root, $foreign))->toThrow(DomainException::class)
        ->and(fn () => $manager->create($customer, $project, ['title' => 'Nope']))->toThrow(DomainException::class);
});

it('logically inactivates empty work groups and never hard deletes them', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $manager = app(WorkGroupManager::class);
    $group = $manager->create($admin, $project, ['title' => 'Temporary']);

    expect(fn () => $group->delete())->toThrow(DomainException::class);

    $manager->inactivate($admin, $group);

    expect($group->refresh()->status)->toBe('inactive')
        ->and(WorkGroup::query()->whereKey($group)->exists())->toBeTrue();
});
