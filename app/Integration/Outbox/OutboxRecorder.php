<?php

namespace App\Integration\Outbox;

use App\Integration\Events\IntegrationEvent;
use App\Models\OutboxMessage;

final class OutboxRecorder
{
    public function __construct(private readonly OutboxMessage $outboxMessages) {}

    public function record(IntegrationEvent $event): void
    {
        $this->outboxMessages->newQuery()->firstOrCreate(['event_id' => $event->eventId()], [
            'event_type' => $event->eventType(),
            'event_version' => $event->version(),
            'correlation_id' => $event->correlationId(),
            'occurred_at' => $event->occurredAt(),
            'payload' => $event->payload(),
        ]);
    }
}
