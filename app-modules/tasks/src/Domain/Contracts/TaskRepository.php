<?php

namespace Modules\Tasks\Domain\Contracts;

interface TaskRepository
{
    /** @param array{actor_id:int,manage_all:bool} $scope */
    public function search(array $scope, ?int $projectId = null, ?string $term = null): array;

    /** @param array{actor_id:int,manage_all:bool} $scope */
    public function findAccessible(int $id, array $scope): array;

    public function create(array $attributes): int;

    public function update(int $id, array $attributes): void;

    public function delete(int $id): void;

    public function addComment(int $taskId, int $userId, string $body): void;
}
