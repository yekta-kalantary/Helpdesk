<?php

namespace Modules\Tasks\Application\Consumers;

use App\Integration\Outbox\ProcessedIntegrationEventRepository;
use Illuminate\Database\ConnectionInterface;
use Modules\Projects\Application\Events\ProjectMembershipRemovedV1;
use Modules\Tasks\Application\Contracts\ProjectTaskStateWriter;

final class ProjectMembershipRemovedConsumer
{
    public function __construct(
        private readonly ProcessedIntegrationEventRepository $processed,
        private readonly ProjectTaskStateWriter $state,
        private readonly ConnectionInterface $connection,
    ) {}

    public function handle(ProjectMembershipRemovedV1 $event): void
    {
        $this->connection->transaction(function () use ($event): void {
            if (! $this->processed->claim($event->eventId(), self::class)) {
                return;
            }

            $this->state->clearAssignmentsForRemovedMember($event->projectId, $event->accountId);
        });
    }
}
