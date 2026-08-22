<?php

namespace App\Integration\Outbox;

use App\Integration\Events\IntegrationEvent;
use App\Models\OutboxMessage;
use InvalidArgumentException;

final class OutboxRecorder
{
    public function __construct(private readonly OutboxMessage $outboxMessages) {}

    public function record(IntegrationEvent $event): void
    {
        $payload = $event->payload();

        foreach ($payload as $value) {
            if (! is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Integration event payload values must be scalar or null.');
            }
        }

        $this->outboxMessages->newQuery()->firstOrCreate(['event_id' => $event->eventId()], [
            'event_type' => $event->eventType(),
            'event_version' => $event->version(),
            'correlation_id' => $event->correlationId(),
            'occurred_at' => $event->occurredAt(),
            'payload' => $payload,
        ]);
    }
}
