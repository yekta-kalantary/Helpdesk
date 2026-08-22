<?php

namespace Modules\Tasks\Application\Contracts;

use Modules\Tasks\Application\DTOs\TaskProjectContext;
use Modules\Tasks\Application\DTOs\TaskStatusContext;
use Modules\Tasks\Application\DTOs\TaskWorkGroupContext;

interface ProjectTaskStateQuery
{
    public function findProject(int $projectId): ?TaskProjectContext;

    public function defaultOpenStatus(int $projectId): ?TaskStatusContext;

    public function findActiveStatus(int $statusId): ?TaskStatusContext;

    public function findActiveWorkGroup(int $workGroupId): ?TaskWorkGroupContext;
}
