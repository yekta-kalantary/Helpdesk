<?php

namespace Modules\Tasks\Application\Consumers;

use App\Integration\Outbox\ProcessedIntegrationEventRepository;
use Modules\Projects\Application\Events\ProjectTaskStatusChangedV1;
use Modules\Tasks\Application\Contracts\ProjectTaskStateWriter;

final class ProjectTaskStatusChangedConsumer
{
    public function __construct(
        private readonly ProcessedIntegrationEventRepository $processed,
        private readonly ProjectTaskStateWriter $state,
    ) {}

    public function handle(ProjectTaskStatusChangedV1 $event): void
    {
        if (! $this->processed->claim($event->eventId(), self::class)) {
            return;
        }

        $this->state->synchronizeStatusCompletion(
            $event->projectId,
            $event->projectTaskStatusId,
            $event->isDone,
            $event->previousDoneStatusId,
        );
    }
}
