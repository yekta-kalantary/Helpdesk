<?php

namespace Modules\Tasks\Infrastructure;

use Modules\Tasks\Domain\Contracts\TaskAttachmentStore;
use Modules\Tasks\Infrastructure\Models\Task;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LocalTaskAttachmentStore implements TaskAttachmentStore
{
    public function add(int $taskId, array $files): void
    {
        $task = Task::query()->findOrFail($taskId);

        foreach ($files as $file) {
            $task->addMedia($file)->toMediaCollection('attachments', 'local');
        }
    }

    public function delete(int $taskId, int $mediaId): void
    {
        $media = Media::query()->findOrFail($mediaId);
        abort_unless($media->model_type === Task::class && (int) $media->model_id === $taskId, 404);
        $media->delete();
    }
}
