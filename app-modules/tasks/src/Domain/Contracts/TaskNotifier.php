<?php

namespace Modules\Tasks\Domain\Contracts;

interface TaskNotifier
{
    public function assigned(int $userId, int $taskId, string $title): void;
}
