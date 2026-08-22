<?php

namespace Modules\Projects\Application\DTOs;

final readonly class ProjectSummary
{
    public function __construct(
        public int $id,
        public int $clientId,
        public bool $isActive,
    ) {}
}
