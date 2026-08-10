<?php

use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Application\Queries\TaskFormOptions;

it('only exposes active project members as task assignees', function (): void {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $inactiveMember = User::factory()->create(['is_active' => false]);

    $project = Project::query()->create([
        'contact_id' => null,
        'category' => 'internal',
        'title' => 'Membership test project',
        'type' => 'other',
        'status' => 'planning',
    ]);
    $project->members()->sync([$member->id, $inactiveMember->id]);

    $options = app(TaskFormOptions::class);
    $result = $options->get($member, ['actor_id' => $member->id, 'manage_all' => true], $project->id);
    $memberIds = collect($result['members'])->pluck('id')->all();

    expect($memberIds)->toContain($member->id)
        ->not->toContain($outsider->id)
        ->not->toContain($inactiveMember->id)
        ->and($options->isAssignableToProject($project->id, $member->id))->toBeTrue()
        ->and($options->isAssignableToProject($project->id, $outsider->id))->toBeFalse()
        ->and($options->isAssignableToProject($project->id, $inactiveMember->id))->toBeFalse();
});
