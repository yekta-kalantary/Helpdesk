<?php

namespace Modules\Tasks\Application;

use App\Notifications\ResourceChangedNotification;
use App\Support\ActivityRecorder;
use App\Support\NotificationDispatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskComment;
use Throwable;

class TaskCollaboration
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
        private readonly TaskNotificationRouter $notificationRouter,
        private readonly AccountAuthenticationEligibility $eligibility,
        private readonly TaskAccess $access,
    ) {}

    public function attach(int $actorId, Task $task, UploadedFile $file, ?TaskComment $comment = null): Attachment
    {
        $this->assertCanCollaborate($actorId, $task);
        $this->validateUploadRate($actorId);
        $this->validateFile($file);

        $path = $file->store('task-attachments/'.$task->id, 'local');

        try {
            $attachment = DB::transaction(function () use ($actorId, $task, $comment, $file, $path): Attachment {
                $attachment = Attachment::query()->create([
                    'task_id' => $task->id,
                    'comment_id' => $comment?->id,
                    'uploaded_by' => $actorId,
                    'original_name' => $file->getClientOriginalName(),
                    'storage_path' => $path,
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                    'size' => $file->getSize(),
                ]);

                $this->recordAttachment($actorId, $task, $attachment);

                return $attachment;
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return $attachment;
    }

    /** @param array<int, UploadedFile> $files */
    public function comment(int $actorId, Task $task, ?string $body, array $files): TaskComment
    {
        $this->assertCanCollaborate($actorId, $task);
        $body = trim((string) $body);

        if ($body === '' && $files === []) {
            throw ValidationException::withMessages([
                'comment' => __('tasks::messages.comment_or_attachment_required'),
            ]);
        }

        foreach ($files as $file) {
            $this->validateUploadRate($actorId);
            $this->validateFile($file);
        }

        $storedPaths = [];
        $createdAttachments = collect();

        try {
            $comment = DB::transaction(function () use ($actorId, $task, $body, $files, &$storedPaths, $createdAttachments): TaskComment {
                $comment = TaskComment::query()->create([
                    'task_id' => $task->id,
                    'user_id' => $actorId,
                    'body' => $body !== '' ? $body : null,
                ]);

                foreach ($files as $file) {
                    $path = $file->store('task-attachments/'.$task->id, 'local');
                    $storedPaths[] = $path;

                    $createdAttachments->push(Attachment::query()->create([
                        'task_id' => $task->id,
                        'comment_id' => $comment->id,
                        'uploaded_by' => $actorId,
                        'original_name' => $file->getClientOriginalName(),
                        'storage_path' => $path,
                        'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                        'size' => $file->getSize(),
                    ]));
                }

                $this->activities->recordIds($actorId, 'comment.added', $task->project_id, $task->id, [
                    'comment_id' => $comment->id,
                    'attachment_count' => count($files),
                ]);

                foreach ($createdAttachments as $attachment) {
                    $this->recordAttachment($actorId, $task, $attachment);
                }

                return $comment;
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($storedPaths);
            throw $e;
        }

        $this->notifications->sendToAccountIds(
            $this->notificationRouter->commentAdded($task),
            new ResourceChangedNotification(
                'نظر جدید روی تسک',
                "برای تسک {$task->reference} نظر جدید ثبت شد.",
                route('tasks.show', $task),
                [
                    'resource_type' => 'task',
                    'resource_id' => $task->id,
                    'reference' => $task->reference,
                ],
            ),
            $actorId,
        );

        return $comment->load('attachments');
    }

    public function hideComment(int $actorId, TaskComment $comment): void
    {
        $this->assertAdmin($actorId);

        if ($comment->hidden_at) {
            return;
        }

        DB::transaction(function () use ($actorId, $comment): void {
            $comment->update(['hidden_at' => now(), 'hidden_by' => $actorId]);
            $this->activities->recordIds($actorId, 'comment.hidden', null, $comment->task_id, [
                'comment_id' => $comment->id,
            ]);
        });
    }

    public function hideAttachment(int $actorId, Attachment $attachment): void
    {
        $this->assertAdmin($actorId);

        if ($attachment->hidden_at) {
            return;
        }

        DB::transaction(function () use ($actorId, $attachment): void {
            $attachment->update(['hidden_at' => now(), 'hidden_by' => $actorId]);
            $this->activities->recordIds($actorId, 'attachment.hidden', null, $attachment->task_id, [
                'attachment_id' => $attachment->id,
                'name' => $attachment->original_name,
            ]);
        });
    }

    private function recordAttachment(int $actorId, Task $task, Attachment $attachment): void
    {
        $this->activities->recordIds($actorId, 'attachment.added', $task->project_id, $task->id, [
            'attachment_id' => $attachment->id,
            'name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'size' => $attachment->size,
        ]);
    }

    private function assertCanCollaborate(int $actorId, Task $task): void
    {
        $this->access->assertMutable($actorId, $task);
    }

    private function assertAdmin(int $actorId): void
    {
        $this->access->assertAdmin($actorId);
    }

    private function validateUploadRate(int $actorId): void
    {
        $key = 'attachment-upload:'.$actorId;

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
