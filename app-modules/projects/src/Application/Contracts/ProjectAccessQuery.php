<?php

namespace Modules\Projects\Application\Contracts;

interface ProjectAccessQuery
{
    public function canAccessProject(int $projectId, int $accountId): bool;
}
