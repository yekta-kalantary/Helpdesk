<?php

namespace Modules\Tasks\Domain\Contracts;

interface TaskAttachmentStore
{
    public function add(int $taskId, array $files): void;

    public function delete(int $taskId, int $mediaId): void;
}
