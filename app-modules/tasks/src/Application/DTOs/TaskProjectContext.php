<?php

namespace Modules\Tasks\Application\DTOs;

final readonly class TaskProjectContext
{
    public function __construct(
        public int $id,
        public int $clientId,
        public bool $isActive,
    ) {}
}
