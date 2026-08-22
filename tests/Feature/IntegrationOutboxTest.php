<?php

use App\Integration\Events\IntegrationEvent;
use App\Integration\Outbox\OutboxRecorder;
use App\Integration\Outbox\ProcessedIntegrationEventRepository;
use App\Models\OutboxMessage;
use Illuminate\Support\Facades\DB;

it('persists an immutable integration event with its scalar payload in the active transaction', function (): void {
    $event = new class implements IntegrationEvent
    {
        public function eventId(): string
        {
            return 'a8d6530c-8a4f-4bc0-a4d2-5291aa1f5c6f';
        }

        public function eventType(): string
        {
            return 'project.membership_removed';
        }

        public function version(): int
        {
            return 1;
        }

        public function occurredAt(): string
        {
            return '2026-08-22T10:15:30+00:00';
        }

        public function correlationId(): string
        {
            return '474a25d7-3172-41b9-901a-7c01c52438b3';
        }

        public function payload(): array
        {
            return [
                'project_id' => 1,
                'account_id' => 2,
                'actor_id' => 3,
                'removed_at' => '2026-08-22T10:15:30+00:00',
            ];
        }
    };

    DB::transaction(function () use ($event): void {
        app(OutboxRecorder::class)->record($event);
    });

    $message = OutboxMessage::query()->sole();

    expect($message->event_id)->toBe('a8d6530c-8a4f-4bc0-a4d2-5291aa1f5c6f')
        ->and($message->event_type)->toBe('project.membership_removed')
        ->and($message->event_version)->toBe(1)
        ->and($message->correlation_id)->toBe('474a25d7-3172-41b9-901a-7c01c52438b3')
        ->and($message->occurred_at->toIso8601String())->toBe('2026-08-22T10:15:30+00:00')
        ->and($message->payload)->toBe([
            'project_id' => 1,
            'account_id' => 2,
            'actor_id' => 3,
            'removed_at' => '2026-08-22T10:15:30+00:00',
        ]);
});

it('claims an integration event once for each consumer', function (): void {
    $repository = app(ProcessedIntegrationEventRepository::class);

    expect($repository->claim('a8d6530c-8a4f-4bc0-a4d2-5291aa1f5c6f', 'audit'))->toBeTrue()
        ->and($repository->claim('a8d6530c-8a4f-4bc0-a4d2-5291aa1f5c6f', 'audit'))->toBeFalse()
        ->and($repository->claim('a8d6530c-8a4f-4bc0-a4d2-5291aa1f5c6f', 'notifications'))->toBeTrue();
});

it('rejects an integration event with a non-scalar payload value', function (): void {
    $event = new class implements IntegrationEvent
    {
        public function eventId(): string
        {
            return 'f6b971b7-934a-4785-98fa-bd5787d0e1b7';
        }

        public function eventType(): string
        {
            return 'project.membership_removed';
        }

        public function version(): int
        {
            return 1;
        }

        public function occurredAt(): string
        {
            return '2026-08-22T10:15:30+00:00';
        }

        public function correlationId(): string
        {
            return 'd2b10194-d501-4d89-8166-6e4d1c7d2f6c';
        }

        public function payload(): array
        {
            return ['project' => ['id' => 1]];
        }
    };

    expect(function () use ($event): void {
        app(OutboxRecorder::class)->record($event);
    })
        ->toThrow(InvalidArgumentException::class)
        ->and(OutboxMessage::query()->count())->toBe(0);
});
