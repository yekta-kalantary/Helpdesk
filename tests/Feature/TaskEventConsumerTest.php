<?php

use App\Models\OutboxMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Modules\Audit\Infrastructure\Models\Activity;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\Events\ProjectMembershipRemovedV1;
use Modules\Projects\Application\Events\ProjectTaskStatusChangedV1;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\Consumers\ProjectMembershipRemovedConsumer;
use Modules\Tasks\Application\Consumers\ProjectTaskStatusChangedConsumer;
use Modules\Tasks\Infrastructure\Models\Task;

it('unassigns removed project members once for duplicate event delivery', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member->id, $admin->id);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'assigned_to' => $member->id,
        'title' => 'Assigned task',
    ]);
    $event = new ProjectMembershipRemovedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        accountId: $member->id,
        actorId: $admin->id,
    );

    app(ProjectMembershipRemovedConsumer::class)->handle($event);
    app(ProjectMembershipRemovedConsumer::class)->handle($event);

    expect($task->fresh()->assigned_to)->toBeNull()
        ->and(Task::query()->whereKey($task)->count())->toBe(1);
});

it('synchronizes task completion when a project status changes done state', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $status = mvpOpenStatus($project);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => $status->id,
        'created_by' => $admin->id,
        'title' => 'Status task',
    ]);
    $doneEvent = new ProjectTaskStatusChangedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        projectTaskStatusId: $status->id,
        previousDoneStatusId: null,
        isDone: true,
        actorId: $admin->id,
    );

    app(ProjectTaskStatusChangedConsumer::class)->handle($doneEvent);

    expect($task->fresh()->completed_at)->not->toBeNull();

    $reopenedEvent = new ProjectTaskStatusChangedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        projectTaskStatusId: mvpDoneStatus($project)->id,
        previousDoneStatusId: $status->id,
        isDone: true,
        actorId: $admin->id,
    );

    app(ProjectTaskStatusChangedConsumer::class)->handle($reopenedEvent);
    app(ProjectTaskStatusChangedConsumer::class)->handle($reopenedEvent);

    expect($task->fresh()->completed_at)->toBeNull()
        ->and(Task::query()->whereKey($task)->count())->toBe(1);
});

it('delivers a committed membership event from the outbox dispatcher to task consumers', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create();
    $project = mvpProject($client);
    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $member->id, $admin->id);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'assigned_to' => $member->id,
        'title' => 'Outbox task',
    ]);

    $manager->remove($project, $member->id, $admin->id);

    expect($task->fresh()->assigned_to)->toBeNull();
});

it('applies duplicate bus delivery of a membership removal exactly once', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member->id, $admin->id);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'assigned_to' => $member->id,
        'title' => 'Redelivered task',
    ]);
    $event = new ProjectMembershipRemovedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        accountId: $member->id,
        actorId: $admin->id,
    );

    Event::dispatch($event);
    Event::dispatch($event);

    expect($task->fresh()->assigned_to)->toBeNull()
        ->and(DB::table('processed_integration_events')->where('consumer', ProjectMembershipRemovedConsumer::class)->count())->toBe(1)
        ->and(Task::query()->whereKey($task)->count())->toBe(1);
});

it('does not duplicate downstream effects when a status change event is redelivered', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $status = mvpOpenStatus($project);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => $status->id,
        'created_by' => $admin->id,
        'assigned_to' => $admin->id,
        'title' => 'Downstream task',
    ]);
    $doneEvent = new ProjectTaskStatusChangedV1(
        eventId: (string) Str::uuid(),
        correlationId: (string) Str::uuid(),
        occurredAt: now()->toIso8601String(),
        projectId: $project->id,
        projectTaskStatusId: $status->id,
        previousDoneStatusId: null,
        isDone: true,
        actorId: $admin->id,
    );

    Event::dispatch($doneEvent);
    $completedAt = $task->fresh()->completed_at;
    $activitiesCount = Activity::query()->count();
    $notificationsCount = DB::table('notifications')->count();

    Event::dispatch($doneEvent);

    expect($task->fresh()->completed_at)->toEqual($completedAt)
        ->and(Activity::query()->count())->toBe($activitiesCount)
        ->and(DB::table('notifications')->count())->toBe($notificationsCount)
        ->and(DB::table('processed_integration_events')->where('consumer', ProjectTaskStatusChangedConsumer::class)->count())->toBe(1);
});

it('keeps a committed membership removal intact and retryable when delivery fails', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member->id, $admin->id);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'assigned_to' => $member->id,
        'title' => 'Retryable task',
    ]);

    $this->app->bind(ProjectMembershipRemovedConsumer::class, fn (): object => new class
    {
        public function handle(ProjectMembershipRemovedV1 $event): void
        {
            throw new RuntimeException('Consumer delivery failed.');
        }
    });

    try {
        app(ProjectMembershipManager::class)->remove($project, $member->id, $admin->id);
    } catch (RuntimeException) {
    }

    $membership = DB::table('project_user')
        ->where('project_id', $project->id)
        ->where('user_id', $member->id)
        ->first();

    expect($membership?->removed_at)->not->toBeNull()
        ->and(OutboxMessage::query()->where('event_type', ProjectMembershipRemovedV1::class)->count())->toBe(1)
        ->and(DB::table('processed_integration_events')->where('consumer', ProjectMembershipRemovedConsumer::class)->count())->toBe(0)
        ->and($task->fresh()->assigned_to)->toBe($member->id);

    $this->app->bind(ProjectMembershipRemovedConsumer::class, ProjectMembershipRemovedConsumer::class);

    $message = OutboxMessage::query()->where('event_type', ProjectMembershipRemovedV1::class)->sole();

    app(ProjectMembershipRemovedConsumer::class)->handle(new ProjectMembershipRemovedV1(
        eventId: $message->event_id,
        correlationId: $message->correlation_id,
        occurredAt: $message->occurred_at->toIso8601String(),
        projectId: (int) $message->payload['project_id'],
        accountId: (int) $message->payload['account_id'],
        actorId: (int) $message->payload['actor_id'],
    ));

    expect($task->fresh()->assigned_to)->toBeNull()
        ->and(DB::table('processed_integration_events')->where('consumer', ProjectMembershipRemovedConsumer::class)->count())->toBe(1);
});
