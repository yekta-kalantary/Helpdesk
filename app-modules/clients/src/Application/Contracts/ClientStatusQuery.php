<?php

namespace Modules\Clients\Application\Contracts;

use Modules\Clients\Application\DTOs\ClientStatusSummary;

interface ClientStatusQuery
{
    public function find(int $clientId): ?ClientStatusSummary;
}
