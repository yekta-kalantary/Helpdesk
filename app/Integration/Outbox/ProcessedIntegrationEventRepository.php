<?php

namespace App\Integration\Outbox;

use Illuminate\Database\ConnectionInterface;

final class ProcessedIntegrationEventRepository
{
    public function __construct(private readonly ConnectionInterface $connection) {}

    public function claim(string $eventId, string $consumer): bool
    {
        return $this->connection->table('processed_integration_events')->insertOrIgnore([
            'event_id' => $eventId,
            'consumer' => $consumer,
            'processed_at' => now(),
        ]) === 1;
    }
}
