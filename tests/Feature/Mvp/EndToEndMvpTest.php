<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Clients\Domain\Enums\ClientStatus;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;

it('E2E-001 onboards a client customer project membership and visible task', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create(['name' => 'Onboarding Client']);
    $customer = User::factory()->customer($client)->create(['email' => 'onboarding@example.test']);
    $project = mvpProject($client, 'Onboarding Project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Onboarding Task',
        'project_status_id' => mvpOpenStatus($project, 1)->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);

    expect($customer->canAuthenticate())->toBeTrue()
        ->and($project->hasActiveMember($customer))->toBeTrue()
        ->and(Task::query()->visibleTo($customer)->whereKey($task)->exists())->toBeTrue();

    $this->actingAs($customer)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('Onboarding Task');
});

it('E2E-002 isolates projects tasks and attachments across clients', function (): void {
    Storage::fake('local');
    $admin = User::query()->admins()->firstOrFail();
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $customerA = User::factory()->customer($clientA)->create();
    $customerB = User::factory()->customer($clientB)->create();
    $projectA = mvpProject($clientA, 'Client A Project');
    $projectB = mvpProject($clientB, 'Client B Project');
    $memberships = app(ProjectMembershipManager::class);
    $memberships->add($projectA, $customerA, $admin);
    $memberships->add($projectB, $customerB, $admin);

    $taskB = app(TaskWorkflow::class)->createForAdmin($admin, $projectB, [
        'title' => 'Client B Secret Task',
        'priority' => TaskPriority::Normal,
    ]);
    $attachmentB = app(TaskCollaboration::class)->attach(
        $customerB,
        $taskB,
        UploadedFile::fake()->create('client-b.pdf', 20, 'application/pdf'),
    );

    $this->actingAs($customerA)
        ->get(route('projects.show', $projectB))
        ->assertNotFound();
    $this->get(route('tasks.show', $taskB))->assertNotFound();
    $this->get(route('attachments.download', $attachmentB))->assertNotFound();
    $this->get(route('tasks.index', ['q' => 'Client B Secret']))
        ->assertOk()
        ->assertDontSee('Client B Secret Task');
});

it('E2E-003 moves a customer request through project workflow without coupling assignment to status', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $workflow = app(TaskWorkflow::class);
    $openA = mvpOpenStatus($project);
    $openB = mvpOpenStatus($project, 1);
    $done = mvpDoneStatus($project);

    $task = $workflow->createForCustomer($customer, $project, ['title' => 'Customer request']);
    expect($task->project_status_id)->toBe($openA->id)->and($task->assigned_to)->toBeNull();

    $task = $workflow->updateByAdmin($admin, $task, [
        'project_status_id' => $openB->id,
        'assigned_to' => $admin->id,
    ]);
    expect($task->project_status_id)->toBe($openB->id)->and($task->assigned_to)->toBe($admin->id);

    $task = $workflow->updateByAdmin($admin, $task, ['assigned_to' => $customer->id]);
    expect($task->project_status_id)->toBe($openB->id)->and($task->assigned_to)->toBe($customer->id);

    $task = $workflow->transitionByCustomer($customer, $task, $openA);
    expect($task->project_status_id)->toBe($openA->id)->and($task->assigned_to)->toBe($customer->id);

    $task = $workflow->transitionByCustomer($customer, $task, $done);
    expect($task->project_status_id)->toBe($done->id)
        ->and($task->assigned_to)->toBe($customer->id)
        ->and($task->completed_at)->not->toBeNull();
});

it('E2E-004 authorizes attachment access through the parent task', function (): void {
    Storage::fake('local');
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $member = User::factory()->customer($client)->create();
    $nonMember = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'File security',
        'priority' => TaskPriority::Normal,
    ]);
    $attachment = app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('authorized.pdf', 20, 'application/pdf'),
    );

    $this->actingAs($member)->get(route('attachments.download', $attachment))->assertOk();
    $this->actingAs($nonMember)->get(route('attachments.download', $attachment))->assertNotFound();
});

it('E2E-005 removes access immediately and reactivates the same membership row', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Membership lifecycle',
        'priority' => TaskPriority::Normal,
    ]);

    $manager->remove($project, $customer, $admin);
    expect(Task::query()->visibleTo($customer)->whereKey($task)->exists())->toBeFalse()
        ->and(DB::table('project_user')->where('project_id', $project->id)->where('user_id', $customer->id)->value('removed_at'))->not->toBeNull();

    $manager->add($project, $customer, $admin);
    expect(DB::table('project_user')->where('project_id', $project->id)->where('user_id', $customer->id)->count())->toBe(1)
        ->and(DB::table('project_user')->where('project_id', $project->id)->where('user_id', $customer->id)->value('removed_at'))->toBeNull()
        ->and(Task::query()->visibleTo($customer)->whereKey($task)->exists())->toBeTrue();
});

it('E2E-006 blocks customers for an inactive client while preserving admin history and membership', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Historical task',
        'priority' => TaskPriority::Normal,
    ]);

    $client->update(['status' => ClientStatus::Inactive]);
    expect($customer->refresh()->canAuthenticate())->toBeFalse()
        ->and($project->hasActiveMember($customer))->toBeTrue();

    $this->actingAs($customer)->get(route('tasks.show', $task))->assertRedirect(route('login'));
    $this->actingAs($admin)->get(route('tasks.show', $task))->assertOk();

    $client->update(['status' => ClientStatus::Active]);
    expect($customer->refresh()->canAuthenticate())->toBeTrue();
    $this->actingAs($customer)->get(route('tasks.show', $task))->assertOk();
});

it('E2E-007 guards project completion makes closed projects read only and allows reopening', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $workflow = app(TaskWorkflow::class);
    $lifecycle = app(ProjectLifecycle::class);
    $done = mvpDoneStatus($project);

    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Open task',
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => $lifecycle->complete($project, $admin))->toThrow(DomainException::class);

    $workflow->changeStatus($admin, $task, $done);
    $lifecycle->complete($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Completed)
        ->and(fn () => $workflow->createForCustomer($customer, $project, ['title' => 'Blocked while closed']))
        ->toThrow(DomainException::class);

    $lifecycle->reopen($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Active)
        ->and($workflow->createForCustomer($customer, $project, ['title' => 'Allowed after reopen']))
        ->toBeInstanceOf(Task::class);
});

it('E2E-008 enforces assignment project status completion and immutable reference invariants', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $outsider = User::factory()->customer($otherClient)->create();
    $project = mvpProject($client);
    $otherProject = mvpProject($client, 'Other workflow');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $workflow = app(TaskWorkflow::class);
    $open = mvpOpenStatus($project);
    $done = mvpDoneStatus($project);

    expect(fn () => $workflow->createForAdmin($admin, $project, [
        'title' => 'Invalid outsider assignment',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $outsider->id,
    ]))->toThrow(DomainException::class);

    $unassigned = $workflow->createForAdmin($admin, $project, [
        'title' => 'Valid unassigned open',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
    ]);
    expect($unassigned->assigned_to)->toBeNull();

    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Integrity',
        'project_status_id' => $open->id,
        'priority' => TaskPriority::Normal,
        'assigned_to' => $customer->id,
    ]);
    $reference = $task->reference;

    $task = $workflow->transitionByCustomer($customer, $task, $done);
    expect($task->completed_at)->not->toBeNull()->and($task->assigned_to)->toBe($customer->id);

    $task = $workflow->transitionByCustomer($customer, $task, $open);
    expect($task->completed_at)->toBeNull()->and($task->assigned_to)->toBe($customer->id);

    expect(fn () => $workflow->changeStatus($admin, $task, mvpOpenStatus($otherProject)))
        ->toThrow(DomainException::class)
        ->and(fn () => $task->update(['reference' => 'TSK-CHANGED']))
        ->toThrow(DomainException::class)
        ->and($task->refresh()->reference)->toBe($reference);
});
