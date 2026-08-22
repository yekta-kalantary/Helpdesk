<?php

namespace Modules\Clients\Application\DTOs;

final readonly class ClientStatusSummary
{
    public function __construct(
        public int $id,
        public bool $isActive,
    ) {}
}
