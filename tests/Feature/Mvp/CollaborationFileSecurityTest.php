<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

it('stores approved files privately and serves them only to project members', function (): void {
    Storage::fake('local');
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($clientA)->create();
    $outsider = User::factory()->customer($clientB)->create();
    $project = mvpProject($clientA);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Private file',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    $attachment = app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('brief.pdf', 50, 'application/pdf'),
    );

    Storage::disk('local')->assertExists($attachment->storage_path);

    $this->actingAs($member)
        ->get(route('attachments.download', $attachment))
        ->assertOk();

    $this->actingAs($outsider)
        ->get(route('attachments.download', $attachment))
        ->assertNotFound();
});

it('blocks customer access to an attachment after its parent comment is hidden', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Hidden comment attachment',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    $comment = app(TaskCollaboration::class)->comment(
        $member,
        $task,
        'Please review the attachment.',
        [UploadedFile::fake()->create('brief.pdf', 50, 'application/pdf')],
    );
    $attachment = $comment->attachments->firstOrFail();

    expect($attachment->comment_id)->toBe($comment->id);

    $this->actingAs($member)
        ->get(route('attachments.download', $attachment))
        ->assertOk();

    app(TaskCollaboration::class)->hideComment($admin, $comment);

    $this->actingAs($member)
        ->get(route('attachments.download', $attachment))
        ->assertNotFound();

    $this->actingAs($admin)
        ->get(route('attachments.download', $attachment))
        ->assertOk();
});

it('rejects executable or disallowed attachment types', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Unsafe file',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('payload.php', 2, 'text/x-php'),
    ))->toThrow(ValidationException::class);
});

it('requires comment text or at least one attachment', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Comment',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => app(TaskCollaboration::class)->comment($member, $task, '   ', []))
        ->toThrow(ValidationException::class);
});

it('blocks new collaboration on terminal tasks and completed projects', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Closed task',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => app(TaskCollaboration::class)->comment($member, $task, 'Late comment', []))
        ->toThrow(DomainException::class);

    $open = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Open before project close',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $project->forceFill(['status' => ProjectStatus::Completed])->save();

    expect(fn () => app(TaskCollaboration::class)->comment($admin, $open, 'Late project comment', []))
        ->toThrow(DomainException::class);
});
