<?php

namespace Modules\Tasks\Application\Contracts;

interface ProjectTaskStateWriter
{
    public function clearAssignmentsForRemovedMember(int $projectId, int $accountId): void;

    public function synchronizeStatusCompletion(int $projectId, int $statusId, bool $isDone, ?int $previousDoneStatusId): void;
}
