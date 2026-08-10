<?php

namespace Modules\Tasks\Infrastructure;

use Modules\Media\Domain\Contracts\MediaManager;
use Modules\Tasks\Domain\Contracts\TaskAttachmentReader;
use Modules\Tasks\Infrastructure\Models\Task;

class MediaTaskAttachmentReader implements TaskAttachmentReader
{
    public function __construct(private readonly MediaManager $media) {}

    public function get(int $taskId, int $mediaId): array
    {
        return $this->media->get(Task::class, $taskId, $mediaId);
    }
}
