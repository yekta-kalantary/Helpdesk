<?php

namespace Modules\Clients\Application\Queries;

use Modules\Clients\Application\DTOs\ClientSummary;
use Modules\Clients\Infrastructure\Models\Client;

final class ActiveClientDirectory
{
    /**
     * @return array<int, ClientSummary>
     */
    public function execute(): array
    {
        return Client::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client): ClientSummary => ClientSummary::fromModel($client))
            ->all();
    }

    /**
     * @param  array<int, int>  $clientIds
     * @return array<int, ClientSummary>
     */
    public function executeForIds(array $clientIds): array
    {
        if ($clientIds === []) {
            return [];
        }

        return Client::query()
            ->active()
            ->whereIn('id', $clientIds)
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client): ClientSummary => ClientSummary::fromModel($client))
            ->all();
    }
}
