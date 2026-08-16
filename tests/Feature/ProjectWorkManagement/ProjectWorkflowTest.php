<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectWorkflowManager;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;

it('bootstraps every new project with a valid project owned workflow', function (): void {
    $project = mvpProject(Client::factory()->create());

    $statuses = $project->taskStatuses()->active()->orderBy('position')->get();

    expect($statuses)->toHaveCount(3)
        ->and($statuses->where('is_done', true))->toHaveCount(1)
        ->and($statuses->where('is_done', false)->count())->toBeGreaterThanOrEqual(1);
});

it('lets only admins manage workflow while preserving invariants', function (): void {
    $client = Client::factory()->create();
    $project = mvpProject($client);
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $manager = app(ProjectWorkflowManager::class);

    expect(fn () => $manager->create($customer, $project, 'بازبینی'))
        ->toThrow(DomainException::class);

    $review = $manager->create($admin, $project, 'بازبینی');
    $review = $manager->rename($admin, $review, 'بازبینی نهایی');
    $manager->setDone($admin, $review);

    $project->refresh();
    $active = $project->taskStatuses()->active()->get();

    expect($review->refresh()->title)->toBe('بازبینی نهایی')
        ->and($review->is_done)->toBeTrue()
        ->and($active->where('is_done', true))->toHaveCount(1)
        ->and($active->where('is_done', false)->count())->toBeGreaterThanOrEqual(1);
});

it('never hard deletes project task statuses and rejects invalid inactivation', function (): void {
    $project = mvpProject(Client::factory()->create());
    $admin = User::factory()->admin()->create();
    $manager = app(ProjectWorkflowManager::class);
    $done = $project->taskStatuses()->active()->where('is_done', true)->firstOrFail();

    expect(fn () => $done->delete())->toThrow(DomainException::class)
        ->and(fn () => $manager->inactivate($admin, $done))->toThrow(DomainException::class);

    $extra = $manager->create($admin, $project, 'آرشیو کاری');
    $manager->inactivate($admin, $extra);

    expect($extra->refresh()->is_active)->toBeFalse()
        ->and($extra->inactivated_at)->not->toBeNull()
        ->and(ProjectTaskStatus::query()->whereKey($extra)->exists())->toBeTrue();
});
