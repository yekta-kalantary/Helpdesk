<?php

namespace Modules\Projects\Application\DTOs;

final readonly class ProjectTaskStatusSummary
{
    public function __construct(
        public int $id,
        public int $projectId,
        public bool $isDone,
    ) {}
}
