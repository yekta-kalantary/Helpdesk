<?php

use App\Models\Activity;
use App\Notifications\ResourceChangedNotification;
use App\Support\CustomerAssignmentRequeuer;
use Illuminate\Support\Facades\Notification;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('requires an active admin actor for automatic customer assignment release', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $nonAdminActor = User::factory()->customer($client)->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);

    expect(fn () => app(CustomerAssignmentRequeuer::class)->requeue($customer, $nonAdminActor))
        ->toThrow(DomainException::class)
        ->and(fn () => app(CustomerAssignmentRequeuer::class)->requeue($customer, $inactiveAdmin))
        ->toThrow(DomainException::class);
});

it('only releases assignments for customer users', function (): void {
    $actor = User::factory()->admin()->create();
    $admin = User::factory()->admin()->create();

    expect(fn () => app(CustomerAssignmentRequeuer::class)->requeue($admin, $actor))
        ->toThrow(DomainException::class);
});

it('releases only scoped open assignments without changing project status and notifies active admins', function (): void {
    Notification::fake();

    $client = Client::factory()->create();
    $actor = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $projectA = mvpProject($client, 'A');
    $projectB = mvpProject($client, 'B');
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($projectA, $customer, $actor);
    $memberships->add($projectB, $customer, $actor);

    $workflow = app(TaskWorkflow::class);
    $openA = mvpOpenStatus($projectA, 1);
    $openB = mvpOpenStatus($projectB, 1);
    $doneA = mvpDoneStatus($projectA);

    $scopedTask = $workflow->createForAdmin($actor, $projectA, [
        'title' => 'Scoped assignment',
        'project_status_id' => $openA->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);
    $otherProjectTask = $workflow->createForAdmin($actor, $projectB, [
        'title' => 'Other project assignment',
        'project_status_id' => $openB->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);
    $doneTask = $workflow->createForAdmin($actor, $projectA, [
        'title' => 'Historical done assignment',
        'project_status_id' => $doneA->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    Notification::fake();

    $released = app(CustomerAssignmentRequeuer::class)->requeue($customer, $actor, $projectA);

    $assigneeActivity = Activity::query()
        ->where('task_id', $scopedTask->id)
        ->where('action', 'task.assignee_changed')
        ->latest('id')
        ->firstOrFail();

    expect($released->modelKeys())->toBe([$scopedTask->id])
        ->and($scopedTask->refresh()->project_status_id)->toBe($openA->id)
        ->and($scopedTask->assigned_to)->toBeNull()
        ->and($scopedTask->completed_at)->toBeNull()
        ->and($otherProjectTask->refresh()->project_status_id)->toBe($openB->id)
        ->and($otherProjectTask->assigned_to)->toBe($customer->id)
        ->and($doneTask->refresh()->project_status_id)->toBe($doneA->id)
        ->and($doneTask->assigned_to)->toBe($customer->id)
        ->and($doneTask->completed_at)->not->toBeNull()
        ->and($assigneeActivity->metadata)->toMatchArray([
            'old' => $customer->id,
            'new' => null,
            'reason' => 'customer_membership_or_account_change',
        ])
        ->and(Activity::query()->where('task_id', $scopedTask->id)->where('action', 'task.status_changed')->exists())->toBeFalse();

    Notification::assertSentTo($otherAdmin, ResourceChangedNotification::class, 1);
    Notification::assertNotSentTo($actor, ResourceChangedNotification::class);
});
