<?php

namespace Modules\Tasks\Application\Actions;

use Modules\Tasks\Domain\Contracts\TaskAttachmentStore;
use Modules\Tasks\Domain\Contracts\TaskRepository;

class SaveTask
{
    public function __construct(
        private readonly TaskRepository $tasks,
        private readonly TaskAttachmentStore $attachments,
    ) {}

    public function execute(?int $id, array $attributes, array $files = [], ?int $previousAssignee = null): int
    {
        if ($id) {
            $this->tasks->update($id, $attributes);
            $taskId = $id;
        } else {
            $taskId = $this->tasks->create($attributes);
        }

        if ($files !== []) {
            $this->attachments->add($taskId, $files);
        }

        return $taskId;
    }
}
