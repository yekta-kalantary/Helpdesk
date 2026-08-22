<?php

namespace Modules\Tasks\Application\DTOs;

final readonly class TaskStatusContext
{
    public function __construct(
        public int $id,
        public int $projectId,
        public bool $isDone,
    ) {}
}
