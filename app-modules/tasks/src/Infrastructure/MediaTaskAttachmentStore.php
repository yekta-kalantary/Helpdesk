<?php

namespace Modules\Tasks\Infrastructure;

use Modules\Media\Domain\Contracts\MediaManager;
use Modules\Tasks\Domain\Contracts\TaskAttachmentStore;
use Modules\Tasks\Infrastructure\Models\Task;

class MediaTaskAttachmentStore implements TaskAttachmentStore
{
    public function __construct(private readonly MediaManager $media) {}

    public function add(int $taskId, array $files): void
    {
        $this->media->add(Task::class, $taskId, 'attachments', $files, 'local');
    }

    public function delete(int $taskId, int $mediaId): void
    {
        $this->media->delete(Task::class, $taskId, $mediaId);
    }
}
