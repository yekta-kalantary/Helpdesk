<?php

namespace App\Integration\Events;

interface IntegrationEvent
{
    public function eventId(): string;

    public function eventType(): string;

    public function version(): int;

    public function occurredAt(): string;

    public function correlationId(): string;

    /** @return array<string, bool|float|int|string|null> */
    public function payload(): array;
}
