<?php

use App\Models\Activity;
use App\Notifications\ResourceChangedNotification;
use App\Support\CustomerAssignmentRequeuer;
use DomainException;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('requires an active admin actor for automatic customer assignment requeue', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $nonAdminActor = User::factory()->customer($client)->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);

    expect(fn () => app(CustomerAssignmentRequeuer::class)->requeue($customer, $nonAdminActor))
        ->toThrow(DomainException::class);

    expect(fn () => app(CustomerAssignmentRequeuer::class)->requeue($customer, $inactiveAdmin))
        ->toThrow(DomainException::class);
});

it('only requeues assignments for customer users', function (): void {
    $actor = User::factory()->admin()->create();
    $admin = User::factory()->admin()->create();

    expect(fn () => app(CustomerAssignmentRequeuer::class)->requeue($admin, $actor))
        ->toThrow(DomainException::class);
});

it('requeues only scoped open assignments with canonical audit and admin queue notification', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $actor = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $projectA = mvpProject($client);
    $projectB = mvpProject($client);
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($projectA, $customer, $actor);
    $memberships->add($projectB, $customer, $actor);

    $workflow = app(TaskWorkflow::class);
    $scopedTask = $workflow->createForAdmin($actor, $projectA, [
        'title' => 'Scoped assignment',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);
    $otherProjectTask = $workflow->createForAdmin($actor, $projectB, [
        'title' => 'Other project assignment',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);
    $terminalTask = $workflow->createForAdmin($actor, $projectA, [
        'title' => 'Historical terminal assignment',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);
    $scopedTask->forceFill(['completed_at' => now()])->save();

    Notification::fake();

    $requeued = app(CustomerAssignmentRequeuer::class)->requeue($customer, $actor, $projectA);

    $assigneeActivity = Activity::query()
        ->where('task_id', $scopedTask->id)
        ->where('action', 'task.assignee_changed')
        ->latest('id')
        ->firstOrFail();
    $statusActivity = Activity::query()
        ->where('task_id', $scopedTask->id)
        ->where('action', 'task.status_changed')
        ->latest('id')
        ->firstOrFail();

    expect($requeued->modelKeys())->toBe([$scopedTask->id])
        ->and($scopedTask->refresh()->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($scopedTask->assigned_to)->toBeNull()
        ->and($scopedTask->completed_at)->toBeNull()
        ->and($otherProjectTask->refresh()->status)->toBe(TaskStatus::InProgress)
        ->and($otherProjectTask->assigned_to)->toBe($customer->id)
        ->and($terminalTask->refresh()->status)->toBe(TaskStatus::Completed)
        ->and($terminalTask->assigned_to)->toBe($customer->id)
        ->and($terminalTask->completed_at)->not->toBeNull()
        ->and($assigneeActivity->metadata)->toMatchArray([
            'old' => $customer->id,
            'new' => null,
        ])
        ->and($statusActivity->metadata)->toMatchArray([
            'old' => TaskStatus::InProgress->value,
            'new' => TaskStatus::WaitingAdmin->value,
        ]);

    Notification::assertSentTo($otherAdmin, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
});
