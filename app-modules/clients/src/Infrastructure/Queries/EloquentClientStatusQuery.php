<?php

namespace Modules\Clients\Infrastructure\Queries;

use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Clients\Application\DTOs\ClientStatusSummary;
use Modules\Clients\Infrastructure\Models\Client;

final class EloquentClientStatusQuery implements ClientStatusQuery
{
    public function find(int $clientId): ?ClientStatusSummary
    {
        $client = Client::query()->find($clientId);

        return $client === null
            ? null
            : new ClientStatusSummary($client->id, $client->isActive());
    }
}
