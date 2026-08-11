<?php

namespace Modules\Tasks\Domain\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
}
