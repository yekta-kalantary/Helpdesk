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
}
