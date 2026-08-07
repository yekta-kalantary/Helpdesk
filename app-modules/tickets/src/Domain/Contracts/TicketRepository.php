<?php

namespace Modules\Tickets\Domain\Contracts;

interface TicketRepository
{
    /** @param array{actor_id:int,customer_id:?int,manage_all:bool} $scope */
    public function search(array $scope, ?int $projectId = null, ?string $term = null): array;

    /** @param array{actor_id:int,customer_id:?int,manage_all:bool} $scope */
    public function findAccessible(int $id, array $scope): array;

    public function create(array $attributes): int;

    public function updateManagement(int $id, string $status, ?int $assignedTo): void;

    public function addMessage(int $ticketId, int $userId, string $body): int;

    public function updateStatus(int $id, string $status): void;

    public function delete(int $id): void;
}
