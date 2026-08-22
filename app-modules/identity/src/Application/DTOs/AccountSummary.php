<?php

namespace Modules\Identity\Application\DTOs;

use Modules\Identity\Domain\Enums\UserRole;

final readonly class AccountSummary
{
    public function __construct(
        public int $id,
        public UserRole $role,
        public bool $isActive,
        public ?int $clientId,
    ) {}
}
