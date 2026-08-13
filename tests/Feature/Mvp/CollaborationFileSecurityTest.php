<?php

use App\Models\Activity;
use App\Support\ActivityRecorder;
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
use Modules\Tasks\Infrastructure\Models\Attachment;

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

it('previews browser-supported attachments with protected inline headers', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Preview file',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $attachment = app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('unsafe name.pdf', 50, 'application/pdf'),
    );

    $this->actingAs($member)
        ->get(route('attachments.preview', $attachment))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename=unsafe-name.pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy', "sandbox; default-src 'none'")
        ->assertHeader('Cache-Control', 'private, no-store')
        ->assertHeader('Referrer-Policy', 'no-referrer');
});

it('keeps non-previewable attachments download-only', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Download-only file',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $attachment = app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('archive.zip', 50, 'application/zip'),
    );

    $this->actingAs($member)
        ->get(route('attachments.preview', $attachment))
        ->assertNotFound();

    $this->actingAs($member)
        ->get(route('attachments.download', $attachment))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename=archive.zip');
});

it('applies attachment authorization to previews including hidden parents', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $outsider = User::factory()->customer(Client::factory()->create())->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Protected preview',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $comment = app(TaskCollaboration::class)->comment(
        $member,
        $task,
        'Preview this',
        [UploadedFile::fake()->create('preview.txt', 1, 'text/plain')],
    );
    $attachment = $comment->attachments->firstOrFail();

    $this->actingAs($outsider)
        ->get(route('attachments.preview', $attachment))
        ->assertNotFound();

    app(TaskCollaboration::class)->hideComment($admin, $comment);

    $this->actingAs($member)
        ->get(route('attachments.preview', $attachment))
        ->assertNotFound();

    $this->actingAs($admin)
        ->get(route('attachments.preview', $attachment))
        ->assertOk();
});

it('enforces hidden collaboration content on direct attachment downloads', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Hidden collaboration attachment',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    $standaloneAttachment = app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('standalone.pdf', 50, 'application/pdf'),
    );
    $comment = app(TaskCollaboration::class)->comment(
        $member,
        $task,
        'Please review the attachment.',
        [UploadedFile::fake()->create('comment.pdf', 50, 'application/pdf')],
    );
    $commentAttachment = $comment->attachments->firstOrFail();

    expect($commentAttachment->comment_id)->toBe($comment->id);

    $this->actingAs($member)
        ->get(route('attachments.download', $commentAttachment))
        ->assertOk();

    app(TaskCollaboration::class)->hideComment($admin, $comment);

    $this->actingAs($member)
        ->get(route('attachments.download', $commentAttachment))
        ->assertNotFound();

    $this->actingAs($member)
        ->get(route('attachments.download', $standaloneAttachment))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('attachments.download', $commentAttachment))
        ->assertOk();

    app(TaskCollaboration::class)->hideAttachment($admin, $standaloneAttachment);

    $this->actingAs($member)
        ->get(route('attachments.download', $standaloneAttachment))
        ->assertNotFound();

    $this->actingAs($admin)
        ->get(route('attachments.download', $standaloneAttachment))
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

it('does not keep a standalone attachment when its activity cannot be recorded', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Atomic standalone attachment',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $file = UploadedFile::fake()->create('atomic.pdf', 50, 'application/pdf');

    $this->mock(ActivityRecorder::class, function ($mock): void {
        $mock->shouldReceive('record')->once()->andThrow(new RuntimeException('activity failed'));
    });

    expect(fn () => app(TaskCollaboration::class)->attach($member, $task, $file))
        ->toThrow(RuntimeException::class);

    expect(Attachment::query()->where('task_id', $task->id)->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([])
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'attachment.added')->count())->toBe(0);
});

it('does not hide a comment when its activity cannot be recorded', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Atomic comment hiding',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $comment = app(TaskCollaboration::class)->comment($member, $task, 'Visible comment', []);

    $this->mock(ActivityRecorder::class, function ($mock): void {
        $mock->shouldReceive('record')->once()->andThrow(new RuntimeException('activity failed'));
    });

    expect(fn () => app(TaskCollaboration::class)->hideComment($admin, $comment))
        ->toThrow(RuntimeException::class);

    expect($comment->refresh()->hidden_at)->toBeNull()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'comment.hidden')->count())->toBe(0);
});

it('does not hide an attachment when its activity cannot be recorded', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Atomic attachment hiding',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $attachment = app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('visible.pdf', 50, 'application/pdf'),
    );

    $this->mock(ActivityRecorder::class, function ($mock): void {
        $mock->shouldReceive('record')->once()->andThrow(new RuntimeException('activity failed'));
    });

    expect(fn () => app(TaskCollaboration::class)->hideAttachment($admin, $attachment))
        ->toThrow(RuntimeException::class);

    expect($attachment->refresh()->hidden_at)->toBeNull()
        ->and(Activity::query()->where('task_id', $task->id)->where('action', 'attachment.hidden')->count())->toBe(0);
});
