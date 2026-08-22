<?php

namespace App\Integration\Outbox;

use App\Integration\Events\IntegrationEvent;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;

final class AfterCommitOutboxDispatcher
{
    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly Dispatcher $events,
    ) {}

    public function dispatch(IntegrationEvent $event): void
    {
        $this->connection->afterCommit(fn (): array => $this->events->dispatch($event));
    }
}
