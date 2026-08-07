<?php

namespace Modules\Tasks\Domain\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Done = 'done';
    case Cancelled = 'cancelled';
}
