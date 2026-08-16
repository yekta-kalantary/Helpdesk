<?php

use App\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Projects\Infrastructure\Models\WorkGroup;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

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

it('rejects a branch move when any descendant would exceed level five', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $manager = app(WorkGroupManager::class);

    $a1 = $manager->create($admin, $project, ['title' => 'A1']);
    $a2 = $manager->create($admin, $project, ['title' => 'A2', 'parent_id' => $a1->id]);
    $manager->create($admin, $project, ['title' => 'A3', 'parent_id' => $a2->id]);

    $b1 = $manager->create($admin, $project, ['title' => 'B1']);
    $b2 = $manager->create($admin, $project, ['title' => 'B2', 'parent_id' => $b1->id]);
    $b3 = $manager->create($admin, $project, ['title' => 'B3', 'parent_id' => $b2->id]);

    expect(fn () => $manager->move($admin, $a1, $b3))->toThrow(DomainException::class)
        ->and($a1->refresh()->parent_id)->toBeNull();
});

it('enforces Work Group inactivation rules while allowing Done task history and independent reopen', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $manager = app(WorkGroupManager::class);
    $workflow = app(TaskWorkflow::class);
    $root = $manager->create($admin, $project, ['title' => 'Root']);
    $child = $manager->create($admin, $project, ['title' => 'Child', 'parent_id' => $root->id]);

    expect(fn () => $manager->inactivate($admin, $root))->toThrow(DomainException::class);

    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Grouped task',
        'work_group_id' => $child->id,
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => $manager->inactivate($admin, $child))->toThrow(DomainException::class);

    $task = $workflow->changeStatus($admin, $task, mvpDoneStatus($project));
    $manager->inactivate($admin, $child);
    $task = $workflow->changeStatus($admin, $task, mvpOpenStatus($project));

    expect($child->refresh()->isActive())->toBeFalse()
        ->and($task->work_group_id)->toBe($child->id)
        ->and($task->isDone())->toBeFalse()
        ->and($task->completed_at)->toBeNull();
});

it('updates description reorders siblings and blocks customer management actions', function (): void {
    $client = Client::factory()->create();
    $project = mvpProject($client);
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $manager = app(WorkGroupManager::class);
    $first = $manager->create($admin, $project, ['title' => 'First', 'description' => 'Old']);
    $second = $manager->create($admin, $project, ['title' => 'Second']);

    $updated = $manager->update($admin, $first, ['description' => 'Updated description']);
    $manager->reorder($admin, $project, [$second->id, $first->id]);

    expect($updated->description)->toBe('Updated description')
        ->and($second->refresh()->position)->toBeLessThan($first->refresh()->position)
        ->and(Activity::query()->where('project_id', $project->id)->where('action', 'work_group.updated')->exists())->toBeTrue()
        ->and(fn () => $manager->update($customer, $first, ['title' => 'Forbidden']))->toThrow(DomainException::class)
        ->and(fn () => $manager->move($customer, $first, null))->toThrow(DomainException::class)
        ->and(fn () => $manager->inactivate($customer, $first))->toThrow(DomainException::class);
});
