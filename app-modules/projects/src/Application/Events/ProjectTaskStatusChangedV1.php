<?php

namespace Modules\Projects\Application\Events;

use App\Integration\Events\IntegrationEvent;

final readonly class ProjectTaskStatusChangedV1 implements IntegrationEvent
{
    public function __construct(
        private string $eventId,
        private string $correlationId,
        private string $occurredAt,
        public int $projectId,
        public int $projectTaskStatusId,
        public bool $isDone,
        public int $actorId,
    ) {}

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function eventType(): string
    {
        return self::class;
    }

    public function version(): int
    {
        return 1;
    }

    public function occurredAt(): string
    {
        return $this->occurredAt;
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }

    public function payload(): array
    {
        return [
            'project_id' => $this->projectId,
            'project_task_status_id' => $this->projectTaskStatusId,
            'is_done' => $this->isDone,
            'actor_id' => $this->actorId,
        ];
    }
}
