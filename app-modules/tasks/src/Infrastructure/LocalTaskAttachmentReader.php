<?php

namespace Modules\Tasks\Infrastructure;

use Modules\Tasks\Domain\Contracts\TaskAttachmentReader;
use Modules\Tasks\Infrastructure\Models\Task;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LocalTaskAttachmentReader implements TaskAttachmentReader
{
    public function get(int $taskId, int $mediaId): array
    {
        $media = Media::query()->findOrFail($mediaId);
        abort_unless($media->model_type === Task::class && (int) $media->model_id === $taskId, 404);

        return [
            'name' => $media->file_name,
            'path' => $media->getPath(),
            'mime_type' => $media->mime_type,
        ];
    }
}
