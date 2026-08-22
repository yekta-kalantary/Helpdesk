<?php

namespace Modules\Projects\Application\DTOs;

final readonly class WorkGroupSummary
{
    public function __construct(
        public int $id,
        public int $projectId,
    ) {}
}
