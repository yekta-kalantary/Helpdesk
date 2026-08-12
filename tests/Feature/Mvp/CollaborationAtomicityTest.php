<?php

use App\Support\ActivityRecorder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Attachment;
use RuntimeException;

it('rolls back a standalone attachment and deletes its stored file when activity recording fails', function (): void {
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

    $recorder = Mockery::mock(ActivityRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('activity write failed'));
    app()->instance(ActivityRecorder::class, $recorder);

    expect(fn () => app(TaskCollaboration::class)->attach(
        $admin,
        $task,
        UploadedFile::fake()->create('brief.pdf', 50, 'application/pdf'),
    ))->toThrow(RuntimeException::class, 'activity write failed');

    expect(Attachment::query()->where('task_id', $task->id)->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('task-attachments/'.$task->id))->toBe([]);
});

it('rolls back comment moderation when activity recording fails', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Atomic comment moderation',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $comment = app(TaskCollaboration::class)->comment($member, $task, 'Keep this auditable.', []);

    $recorder = Mockery::mock(ActivityRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('activity write failed'));
    app()->instance(ActivityRecorder::class, $recorder);

    expect(fn () => app(TaskCollaboration::class)->hideComment($admin, $comment))
        ->toThrow(RuntimeException::class, 'activity write failed');

    expect($comment->refresh()->hidden_at)->toBeNull()
        ->and($comment->hidden_by)->toBeNull();
});

it('rolls back attachment moderation when activity recording fails', function (): void {
    Storage::fake('local');

    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Atomic attachment moderation',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);
    $attachment = app(TaskCollaboration::class)->attach(
        $member,
        $task,
        UploadedFile::fake()->create('brief.pdf', 50, 'application/pdf'),
    );

    $recorder = Mockery::mock(ActivityRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('activity write failed'));
    app()->instance(ActivityRecorder::class, $recorder);

    expect(fn () => app(TaskCollaboration::class)->hideAttachment($admin, $attachment))
        ->toThrow(RuntimeException::class, 'activity write failed');

    expect($attachment->refresh()->hidden_at)->toBeNull()
        ->and($attachment->hidden_by)->toBeNull();
});
