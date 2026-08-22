<?php

namespace Modules\Tasks\Application\DTOs;

final readonly class TaskWorkGroupContext
{
    public function __construct(
        public int $id,
        public int $projectId,
    ) {}
}
