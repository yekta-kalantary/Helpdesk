<?php

namespace Modules\Tasks\Application;

use App\Notifications\ResourceChangedNotification;
use App\Support\ActivityRecorder;
use App\Support\NotificationDispatcher;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskComment;
use Throwable;

class TaskCollaboration
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function attach(User $actor, Task $task, UploadedFile $file, ?TaskComment $comment = null): Attachment
    {
        $this->assertCanCollaborate($actor, $task);
        $this->validateUploadRate($actor);
        $this->validateFile($file);

        $path = $file->store('task-attachments/'.$task->id, 'local');

        try {
            $attachment = Attachment::query()->create([
                'task_id' => $task->id,
                'comment_id' => $comment?->id,
                'uploaded_by' => $actor->id,
                'original_name' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        $this->recordAttachment($actor, $task, $attachment);

        return $attachment;
    }

    /** @param array<int, UploadedFile> $files */
    public function comment(User $actor, Task $task, ?string $body, array $files): TaskComment
    {
        $this->assertCanCollaborate($actor, $task);
        $body = trim((string) $body);

        if ($body === '' && $files === []) {
            throw ValidationException::withMessages([
                'comment' => __('tasks::messages.comment_or_attachment_required'),
            ]);
        }

        foreach ($files as $file) {
            $this->validateUploadRate($actor);
            $this->validateFile($file);
        }

        $storedPaths = [];
        $createdAttachments = collect();

        try {
            $comment = DB::transaction(function () use ($actor, $task, $body, $files, &$storedPaths, $createdAttachments): TaskComment {
                $comment = TaskComment::query()->create([
                    'task_id' => $task->id,
                    'user_id' => $actor->id,
                    'body' => $body !== '' ? $body : null,
                ]);

                foreach ($files as $file) {
                    $path = $file->store('task-attachments/'.$task->id, 'local');
                    $storedPaths[] = $path;

                    $createdAttachments->push(Attachment::query()->create([
                        'task_id' => $task->id,
                        'comment_id' => $comment->id,
                        'uploaded_by' => $actor->id,
                        'original_name' => $file->getClientOriginalName(),
                        'storage_path' => $path,
                        'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                        'size' => $file->getSize(),
                    ]));
                }

                $this->activities->record($actor, 'comment.added', $task->project, $task, [
                    'comment_id' => $comment->id,
                    'attachment_count' => count($files),
                ]);

                foreach ($createdAttachments as $attachment) {
                    $this->recordAttachment($actor, $task, $attachment);
                }

                return $comment;
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($storedPaths);
            throw $e;
        }

        $task->loadMissing(['project.activeMembers', 'creator', 'assignee']);
        $recipients = collect($task->project->activeMembers)
            ->push($task->creator)
            ->when($task->assignee, fn ($users) => $users->push($task->assignee));

        $this->notifications->send(
            $recipients,
            new ResourceChangedNotification(
                'نظر جدید روی تسک',
                "برای تسک {$task->reference} نظر جدید ثبت شد.",
                url('/tasks/'.$task->id),
                [
                    'resource_type' => 'task',
                    'resource_id' => $task->id,
                    'reference' => $task->reference,
                ],
            ),
            $actor,
        );

        return $comment->load('attachments');
    }

    public function hideComment(User $actor, TaskComment $comment): void
    {
        $this->assertAdmin($actor);

        if ($comment->hidden_at) {
            return;
        }

        $comment->update(['hidden_at' => now(), 'hidden_by' => $actor->id]);
        $this->activities->record($actor, 'comment.hidden', $comment->task->project, $comment->task, [
            'comment_id' => $comment->id,
        ]);
    }

    public function hideAttachment(User $actor, Attachment $attachment): void
    {
        $this->assertAdmin($actor);

        if ($attachment->hidden_at) {
            return;
        }

        $attachment->update(['hidden_at' => now(), 'hidden_by' => $actor->id]);
        $this->activities->record($actor, 'attachment.hidden', $attachment->task->project, $attachment->task, [
            'attachment_id' => $attachment->id,
            'name' => $attachment->original_name,
        ]);
    }

    private function recordAttachment(User $actor, Task $task, Attachment $attachment): void
    {
        $this->activities->record($actor, 'attachment.added', $task->project, $task, [
            'attachment_id' => $attachment->id,
            'name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'size' => $attachment->size,
        ]);
    }

    private function assertCanCollaborate(User $actor, Task $task): void
    {
        if (! $actor->is_active || ! $actor->canAuthenticate()) {
            throw new DomainException('An active account is required.');
        }

        if (! $actor->isAdmin() && ! Task::query()->visibleTo($actor)->whereKey($task->id)->exists()) {
            throw new DomainException('Task access is not allowed.');
        }

        $project = $task->project()->with('client')->firstOrFail();

        if (! $project->isActive() || ! $project->client->isActive() || $task->isTerminal()) {
            throw new DomainException('Closed projects and tasks are read-only for collaboration.');
        }
    }

    private function assertAdmin(User $actor): void
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw new DomainException('Only an active admin may hide collaboration content.');
        }
    }

    private function validateUploadRate(User $actor): void
    {
        $key = 'attachment-upload:'.$actor->id;

        if (! RateLimiter::attempt($key, 20, static fn (): bool => true, 60)) {
            throw ValidationException::withMessages([
                'attachments' => __('tasks::messages.too_many_uploads'),
            ]);
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'file',
                    'max:'.config('helpdesk.attachments.max_kilobytes', 20480),
                    'extensions:'.implode(',', config('helpdesk.attachments.extensions', [])),
                    'mimetypes:'.implode(',', config('helpdesk.attachments.mime_types', [])),
                ],
            ],
        )->validate();
    }
}
